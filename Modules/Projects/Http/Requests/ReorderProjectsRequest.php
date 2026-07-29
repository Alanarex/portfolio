<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Projects\Models\Project;

final class ReorderProjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'max:200'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:projects,id'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (count((array) $this->input('ids', [])) !== Project::query()->count()) {
                $validator->errors()->add('ids', 'La réorganisation doit inclure tous les projets.');
            }
        }];
    }
}
