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

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => __('portfolio.contact.errors.name_required'),
            'name.max' => __('portfolio.contact.errors.name_max', ['max' => 120]),
            'name.not_regex' => __('portfolio.contact.errors.single_line'),
            'email.required' => __('portfolio.contact.errors.email_required'),
            'email.email' => __('portfolio.contact.errors.email_invalid'),
            'email.max' => __('portfolio.contact.errors.email_invalid'),
            'email.not_regex' => __('portfolio.contact.errors.single_line'),
            'subject.max' => __('portfolio.contact.errors.subject_max', ['max' => 160]),
            'subject.not_regex' => __('portfolio.contact.errors.single_line'),
            'message.required' => __('portfolio.contact.errors.message_required'),
            'message.min' => __('portfolio.contact.errors.message_min', ['min' => 10]),
            'message.max' => __('portfolio.contact.errors.message_max', ['max' => 5000]),
        ];
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
