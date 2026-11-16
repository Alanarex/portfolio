<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class UpdateAccountProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-account');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique(User::class, 'email')->ignore($this->user()?->getKey())],
            'current_password' => ['nullable', 'string', 'max:4096'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => __('account.validation.name_required'),
            'name.max' => __('account.validation.name_max'),
            'email.required' => __('account.validation.email_required'),
            'email.email' => __('account.validation.email_invalid'),
            'email.max' => __('account.validation.email_invalid'),
            'email.unique' => __('account.validation.email_unavailable'),
        ];
    }
}
