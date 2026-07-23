<?php

declare(strict_types=1);

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:120'],
            'is_published' => ['required', 'boolean'],
            'show_location' => ['required', 'boolean'],
            'show_availability' => ['required', 'boolean'],
            'translations' => ['required', 'array:fr,en,ar'],
            'translations.fr' => ['required', 'array'],
            'translations.en' => ['required', 'array'],
            'translations.ar' => ['nullable', 'array'],
            'translations.fr.professional_titles' => ['required', 'array', 'min:1', 'max:5'],
            'translations.en.professional_titles' => ['required', 'array', 'min:1', 'max:5'],
            'translations.ar.professional_titles' => ['nullable', 'array', 'max:5'],
            'translations.fr.professional_titles.*' => ['required', 'string', 'max:120'],
            'translations.en.professional_titles.*' => ['required', 'string', 'max:120'],
            'translations.ar.professional_titles.*' => ['nullable', 'string', 'max:120'],
            'translations.fr.summary' => ['required', 'string', 'max:1000'],
            'translations.en.summary' => ['required', 'string', 'max:1000'],
            'translations.ar.summary' => ['nullable', 'string', 'max:1000'],
            'translations.*.location_label' => ['nullable', 'string', 'max:160'],
            'translations.*.availability' => ['nullable', 'string', 'max:240'],
            'translations.fr.biography' => ['required', 'string', 'max:10000'],
            'translations.en.biography' => ['required', 'string', 'max:10000'],
            'translations.ar.biography' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
