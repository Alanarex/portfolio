<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;
use Modules\Projects\Enums\MediaKind;
use Modules\Projects\Infrastructure\MediaUploadPolicy;

final class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'kind' => ['required', Rule::enum(MediaKind::class)],
            'file' => ['required', File::types(['jpg', 'jpeg', 'png', 'webp', 'pdf'])->max(20 * 1024)],
            'is_public' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'between:0,1000000'],
            'translations' => ['required', 'array:fr,en'],
            'translations.fr' => ['required', 'array'],
            'translations.en' => ['required', 'array'],
            'translations.*.alt_text' => ['nullable', 'string', 'max:240'],
            'translations.*.caption' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $kindValue = $this->input('kind');
            $file = $this->file('file');
            if (! is_string($kindValue) || ! $file instanceof UploadedFile || ! $file->isValid()) {
                return;
            }

            $kind = MediaKind::tryFrom($kindValue);
            if ($kind === null) {
                return;
            }
            $policy = MediaUploadPolicy::for($kind);
            $mimeType = $file->getMimeType();
            $extension = mb_strtolower($file->getClientOriginalExtension());
            if (! is_string($mimeType) || ! in_array($extension, $policy['mimes'][$mimeType] ?? [], true)) {
                $validator->errors()->add('file', 'Le type MIME et l’extension ne correspondent pas au média sélectionné.');
            }
            if (($file->getSize() ?: 0) > $policy['max_bytes']) {
                $validator->errors()->add('file', 'La taille du fichier dépasse la limite autorisée pour ce type de média.');
            }

            $this->validatePublicTranslations($validator);
        }];
    }

    private function validatePublicTranslations(Validator $validator): void
    {
        if (! $this->boolean('is_public')) {
            return;
        }
        foreach (['fr', 'en'] as $locale) {
            if (! filled($this->input("translations.{$locale}.alt_text"))) {
                $validator->errors()->add("translations.{$locale}.alt_text", 'Un texte alternatif français et anglais est requis avant publication.');
            }
        }
    }
}
