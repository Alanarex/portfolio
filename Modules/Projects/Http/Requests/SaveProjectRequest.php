<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Projects\Enums\CaseStudySectionType;
use Modules\Projects\Enums\ProjectLifecycle;
use Modules\Projects\Enums\PublicationStatus;
use Modules\Projects\Enums\RepositoryVisibility;
use Modules\Projects\Models\Project;

final class SaveProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'slug' => [
                'required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('projects', 'slug')->ignore($project instanceof Project ? $project->getKey() : null),
            ],
            'lifecycle_status' => ['required', Rule::enum(ProjectLifecycle::class)],
            'technologies' => ['required', 'array', 'max:50'],
            'technologies.*' => ['required', 'string', 'max:80', 'distinct'],
            'start_year' => ['nullable', 'integer', 'between:1950,2100'],
            'start_month' => ['nullable', 'integer', 'between:1,12'],
            'end_year' => ['nullable', 'integer', 'between:1950,2100'],
            'end_month' => ['nullable', 'integer', 'between:1,12'],
            'is_ongoing' => ['required', 'boolean'],
            'repository_visibility' => ['required', Rule::enum(RepositoryVisibility::class)],
            'repository_url' => ['nullable', 'url:https', 'max:2048'],
            'show_repository' => ['required', 'boolean'],
            'demo_url' => ['nullable', 'url:https', 'max:2048'],
            'show_demo' => ['required', 'boolean'],
            'publication_status' => ['required', Rule::enum(PublicationStatus::class)],
            'is_featured' => ['required', 'boolean'],
            'featured_order' => ['nullable', 'integer', 'between:0,1000000'],
            'sort_order' => ['required', 'integer', 'between:0,1000000'],
            'translations' => ['required', 'array:fr,en'],
            'translations.fr' => ['nullable', 'array'],
            'translations.en' => ['nullable', 'array'],
            'translations.*.title' => ['nullable', 'string', 'max:180'],
            'translations.*.summary' => ['nullable', 'string', 'max:5000'],
            'translations.*.role' => ['nullable', 'string', 'max:180'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:180'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:320'],
            'sections' => ['required', 'array', 'max:8'],
            'sections.*.id' => ['nullable', 'integer', 'exists:case_study_sections,id', 'distinct'],
            'sections.*.type' => ['required', Rule::enum(CaseStudySectionType::class), 'distinct'],
            'sections.*.is_public' => ['required', 'boolean'],
            'sections.*.is_verified' => ['required', 'boolean'],
            'sections.*.sort_order' => ['required', 'integer', 'between:0,1000000'],
            'sections.*.translations' => ['required', 'array:fr,en'],
            'sections.*.translations.fr' => ['nullable', 'array'],
            'sections.*.translations.en' => ['nullable', 'array'],
            'sections.*.translations.*.heading' => ['nullable', 'string', 'max:180'],
            'sections.*.translations.*.body' => ['nullable', 'string', 'max:20000'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $project = $this->route('project');
            if ($project instanceof Project && $this->input('slug') !== $project->slug) {
                $validator->errors()->add('slug', 'Le slug public est stable après création.');
            }

            if ($this->boolean('show_repository')) {
                if ($this->input('repository_visibility') !== RepositoryVisibility::Public->value) {
                    $validator->errors()->add('show_repository', 'Seul un dépôt explicitement public peut être affiché.');
                }
                if (! filled($this->input('repository_url'))) {
                    $validator->errors()->add('repository_url', 'Une URL HTTPS est requise pour afficher le dépôt.');
                }
            }
            if ($this->input('repository_visibility') === RepositoryVisibility::Private->value && $this->boolean('show_repository')) {
                $validator->errors()->add('repository_visibility', 'Les informations d’un dépôt privé ne peuvent pas être publiées.');
            }
            if ($this->boolean('show_demo') && ! filled($this->input('demo_url'))) {
                $validator->errors()->add('demo_url', 'Une URL HTTPS est requise pour afficher la démo.');
            }
            if ($this->boolean('is_featured') && ! is_numeric($this->input('featured_order'))) {
                $validator->errors()->add('featured_order', 'Un ordre est requis pour un projet mis en avant.');
            }

            $this->validateDates($validator);
            $published = $this->input('publication_status') === PublicationStatus::Published->value;
            if ($published && count((array) $this->input('technologies', [])) === 0) {
                $validator->errors()->add('technologies', 'Au moins une technologie vérifiée est requise avant publication.');
            }
            if ($published && $this->input('lifecycle_status') === ProjectLifecycle::Unspecified->value) {
                $validator->errors()->add('lifecycle_status', 'Le statut du projet doit être vérifié avant publication.');
            }
            if ($published) {
                foreach (['fr', 'en'] as $locale) {
                    $translation = $this->input("translations.{$locale}");
                    if (! is_array($translation)
                        || ! filled($translation['title'] ?? null)
                        || ! filled($translation['summary'] ?? null)
                        || ! filled($translation['role'] ?? null)) {
                        $validator->errors()->add("translations.{$locale}", 'Un titre, un résumé et un rôle complets sont requis en français et en anglais.');
                    }
                }
            }

            foreach ((array) $this->input('sections', []) as $index => $section) {
                if (! is_array($section) || ($section['is_public'] ?? false) !== true) {
                    continue;
                }
                if (($section['is_verified'] ?? false) !== true) {
                    $validator->errors()->add("sections.{$index}.is_verified", 'Une section doit être vérifiée avant d’être publique.');
                }
                foreach (['fr', 'en'] as $locale) {
                    $translation = $section['translations'][$locale] ?? null;
                    if (! is_array($translation)
                        || ! filled($translation['heading'] ?? null)
                        || ! filled($translation['body'] ?? null)) {
                        $validator->errors()->add("sections.{$index}.translations.{$locale}", 'Une section publique requiert un titre et un contenu complets en français et en anglais.');
                    }
                }
            }
        }];
    }

    private function validateDates(Validator $validator): void
    {
        $startYear = $this->input('start_year');
        $endYear = $this->input('end_year');
        if ($this->boolean('is_ongoing') || ! is_numeric($startYear) || ! is_numeric($endYear)) {
            return;
        }

        $start = ((int) $startYear * 12) + (int) $this->input('start_month', 0);
        $end = ((int) $endYear * 12) + (int) $this->input('end_month', 12);
        if ($end < $start) {
            $validator->errors()->add('end_year', 'La fin du projet doit être postérieure à son début.');
        }
    }
}
