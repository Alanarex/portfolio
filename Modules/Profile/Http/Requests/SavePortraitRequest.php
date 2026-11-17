<?php

declare(strict_types=1);

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SavePortraitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'portrait' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'portrait.required' => 'Choisissez une image.',
            'portrait.mimes' => 'La photo doit être au format JPEG, PNG ou WebP.',
            'portrait.mimetypes' => 'La photo doit être au format JPEG, PNG ou WebP.',
            'portrait.max' => 'La photo ne doit pas dépasser 5 Mo.',
        ];
    }
}
