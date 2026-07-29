<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
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
            if (! $this->boolean('is_public')) {
                return;
            }
            foreach (['fr', 'en'] as $locale) {
                if (! filled($this->input("translations.{$locale}.alt_text"))) {
                    $validator->errors()->add("translations.{$locale}.alt_text", 'Un texte alternatif français et anglais est requis avant publication.');
                }
            }
        }];
    }
}
