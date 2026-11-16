<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\AdministratorPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class UpdateAccountPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-account');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'max:4096'],
            'password' => ['required', 'string', 'max:4096', AdministratorPassword::rule(), 'different:current_password', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'current_password.required' => __('account.validation.current_required'),
            'password.required' => __('account.validation.password_required'),
            'password.max' => __('account.validation.password_max'),
            'password.different' => __('account.validation.password_different'),
            'password.confirmed' => __('account.validation.password_confirmed'),
        ];
    }
}
