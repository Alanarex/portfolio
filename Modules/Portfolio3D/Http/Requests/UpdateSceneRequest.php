<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Portfolio3D\Enums\HotspotKey;

final class UpdateSceneRequest extends FormRequest
{
    private const ASSET_PATH = '/^\/(?!.*\.\.)[A-Za-z0-9][A-Za-z0-9._\/-]*\.(?:webp|png|jpe?g|avif)$/';

    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'asset_version' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9][a-z0-9._-]*$/'],
            'poster_path' => ['nullable', 'string', 'max:255', 'regex:'.self::ASSET_PATH],
            'mobile_fallback_path' => ['nullable', 'string', 'max:255', 'regex:'.self::ASSET_PATH],
            'greeting_fr' => ['nullable', 'string', 'max:280'],
            'greeting_en' => ['nullable', 'string', 'max:280'],
            'performance_mode' => ['required', Rule::in(['auto', 'standard', 'low-power'])],
            'reduced_motion_mode' => ['required', Rule::in(['respect', 'always'])],
            'hotspots' => ['required', 'array', 'size:'.count(HotspotKey::cases())],
            'hotspots.*' => ['array:key,target,is_visible,sort_order,label_fr,label_en,description_fr,description_en'],
            'hotspots.*.key' => ['required', 'distinct', Rule::in(HotspotKey::values())],
            'hotspots.*.target' => ['required', Rule::in(HotspotKey::TARGETS)],
            'hotspots.*.is_visible' => ['required', 'boolean'],
            'hotspots.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
            'hotspots.*.label_fr' => ['nullable', 'string', 'max:80'],
            'hotspots.*.label_en' => ['nullable', 'string', 'max:80'],
            'hotspots.*.description_fr' => ['nullable', 'string', 'max:280'],
            'hotspots.*.description_en' => ['nullable', 'string', 'max:280'],
        ];
    }
}
