<?php

declare(strict_types=1);

namespace Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:120'],
            'default_locale' => ['required', Rule::in(['fr'])],
            'contact_email' => ['nullable', 'email:rfc', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+(). -]*$/'],
            'show_email' => ['required', 'boolean'],
            'show_phone' => ['required', 'boolean'],
            'social_links' => ['required', 'array', 'max:5'],
            'social_links.*' => ['array:platform,url,is_enabled,is_public,sort_order'],
            'social_links.*.platform' => ['required', 'distinct', Rule::in(['github', 'gitlab', 'linkedin', 'whatsapp', 'scheduling'])],
            'social_links.*.url' => ['required', 'url:http,https', 'max:2048'],
            'social_links.*.is_enabled' => ['required', 'boolean'],
            'social_links.*.is_public' => ['required', 'boolean'],
            'social_links.*.sort_order' => ['required', 'integer', 'min:0', 'max:100'],
            'feature_flags' => ['required', 'array:projects,contact,cv,three_d'],
            'feature_flags.projects' => ['required', 'boolean'],
            'feature_flags.contact' => ['required', 'boolean'],
            'feature_flags.cv' => ['required', 'boolean'],
            'feature_flags.three_d' => ['required', 'boolean'],
        ];
    }
}
