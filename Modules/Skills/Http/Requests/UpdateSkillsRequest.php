<?php

declare(strict_types=1);

namespace Modules\Skills\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Skills\Enums\PublicationStatus;

final class UpdateSkillsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $statuses = array_column(PublicationStatus::cases(), 'value');

        return [
            'categories' => ['required', 'array', 'max:50'],
            'categories.*.id' => ['nullable', 'integer', 'exists:skill_categories,id', 'distinct'],
            'categories.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'distinct'],
            'categories.*.status' => ['required', Rule::in($statuses)],
            'categories.*.is_visible' => ['required', 'boolean'],
            'categories.*.sort_order' => ['required', 'integer', 'between:0,1000000'],
            'categories.*.translations' => ['required', 'array:fr,en'],
            'categories.*.translations.fr' => ['nullable', 'array'],
            'categories.*.translations.en' => ['nullable', 'array'],
            'categories.*.translations.*.name' => ['nullable', 'string', 'max:120'],
            'categories.*.skills' => ['required', 'array', 'max:200'],
            'categories.*.skills.*.id' => ['nullable', 'integer', 'exists:skills,id', 'distinct'],
            'categories.*.skills.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'categories.*.skills.*.status' => ['required', Rule::in($statuses)],
            'categories.*.skills.*.is_visible' => ['required', 'boolean'],
            'categories.*.skills.*.sort_order' => ['required', 'integer', 'between:0,1000000'],
            'categories.*.skills.*.translations' => ['required', 'array:fr,en'],
            'categories.*.skills.*.translations.fr' => ['nullable', 'array'],
            'categories.*.skills.*.translations.en' => ['nullable', 'array'],
            'categories.*.skills.*.translations.*.name' => ['nullable', 'string', 'max:120'],
            'categories.*.skills.*.translations.*.description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $skillKeys = [];
            foreach ((array) $this->input('categories', []) as $categoryIndex => $category) {
                if (! is_array($category)) {
                    continue;
                }
                $published = ($category['status'] ?? null) === PublicationStatus::Published->value;
                foreach (['fr', 'en'] as $locale) {
                    if ($published && ! filled($category['translations'][$locale]['name'] ?? null)) {
                        $validator->errors()->add("categories.{$categoryIndex}.translations.{$locale}.name", 'Une traduction complète FR et EN est requise avant publication.');
                    }
                }

                foreach ((array) ($category['skills'] ?? []) as $skillIndex => $skill) {
                    if (! is_array($skill)) {
                        continue;
                    }
                    $key = $skill['key'] ?? null;
                    if (is_string($key) && in_array($key, $skillKeys, true)) {
                        $validator->errors()->add("categories.{$categoryIndex}.skills.{$skillIndex}.key", 'La clé de compétence doit être unique.');
                    }
                    if (is_string($key)) {
                        $skillKeys[] = $key;
                    }
                    if (($skill['status'] ?? null) === PublicationStatus::Published->value) {
                        foreach (['fr', 'en'] as $locale) {
                            if (! filled($skill['translations'][$locale]['name'] ?? null)) {
                                $validator->errors()->add("categories.{$categoryIndex}.skills.{$skillIndex}.translations.{$locale}.name", 'Une traduction complète FR et EN est requise avant publication.');
                            }
                        }
                    }
                }
            }
        }];
    }
}
