<?php

declare(strict_types=1);

namespace Modules\Contact\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitContactRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $locale = $this->route('locale');
        if (is_string($locale) && in_array($locale, ['fr', 'en'], true)) {
            app()->setLocale($locale);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', 'not_regex:/[\r\n]/'],
            'email' => ['required', 'email:rfc', 'max:255', 'not_regex:/[\r\n]/'],
            'subject' => ['nullable', 'string', 'max:160', 'not_regex:/[\r\n]/'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'string', 'max:200'],
        ];
    }
}
