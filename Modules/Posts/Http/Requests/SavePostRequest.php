<?php

declare(strict_types=1);

namespace Modules\Posts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Posts\Enums\PostStatus;

final class SavePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locale' => ['required', Rule::in(['fr', 'en'])],
            'body' => ['required', 'string', 'min:3', 'max:3000'],
            'link_url' => ['nullable', 'url:https', 'max:2048', 'required_with:link_label'],
            'link_label' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::enum(PostStatus::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'locale' => 'langue',
            'body' => 'texte',
            'link_url' => 'lien',
            'link_label' => 'libellé du lien',
            'status' => 'statut',
        ];
    }
}
