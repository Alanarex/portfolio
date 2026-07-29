<?php

declare(strict_types=1);

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Profile\Models\CvVersion;
use Modules\Profile\Models\Profile;

final class SaveCvVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $profileId = Profile::query()->where('key', 'main')->value('id');
        $cvVersion = $this->route('cvVersion');

        return [
            'locale' => ['required', Rule::in(['fr', 'en', 'ar'])],
            'label' => ['required', 'string', 'max:120'],
            'version_label' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/',
                Rule::unique('cv_versions')->where(fn ($query) => $query
                    ->where('profile_id', $profileId)
                    ->where('locale', $this->input('locale')))
                    ->ignore($cvVersion instanceof CvVersion ? $cvVersion->getKey() : null),
            ],
            'document' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:20480'],
            'is_verified' => ['required', 'boolean'],
            'published' => ['required', 'boolean'],
            'archived' => ['required', 'boolean'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->boolean('published') && ! $this->boolean('is_verified')) {
                $validator->errors()->add('published', 'Une version publiée doit être vérifiée.');
            }

            if ($this->boolean('published') && $this->boolean('archived')) {
                $validator->errors()->add('archived', 'Une version archivée ne peut pas être publiée.');
            }

            $current = $this->route('cvVersion');
            $hasStoredDocument = $current instanceof CvVersion && $current->path !== null;
            if (($this->boolean('published') || $this->boolean('is_verified'))
                && ! $this->hasFile('document')
                && ! $hasStoredDocument) {
                $validator->errors()->add('document', 'Un fichier PDF est requis avant vérification ou publication.');
            }
        }];
    }
}
