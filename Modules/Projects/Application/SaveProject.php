<?php

declare(strict_types=1);

namespace Modules\Projects\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Projects\Enums\PublicationStatus;
use Modules\Projects\Enums\RepositoryVisibility;
use Modules\Projects\Infrastructure\ProjectsCache;
use Modules\Projects\Models\CaseStudySection;
use Modules\Projects\Models\Project;

final class SaveProject
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(
        array $data,
        User $actor,
        ?string $requestId,
        ?Project $project = null,
    ): Project {
        $changed = false;
        $project = DB::transaction(function () use ($data, $actor, $requestId, $project, &$changed): Project {
            if ($project?->exists === true) {
                $project = Project::query()->lockForUpdate()->findOrFail($project->getKey());
            } else {
                $project = new Project(['uuid' => (string) Str::uuid()]);
            }
            $created = ! $project->exists;
            $project->fill([
                'slug' => $data['slug'],
                'lifecycle_status' => $data['lifecycle_status'],
                'technologies' => array_values(array_unique($data['technologies'])),
                'start_year' => $data['start_year'] ?? null,
                'start_month' => $data['start_month'] ?? null,
                'end_year' => $data['is_ongoing'] ? null : ($data['end_year'] ?? null),
                'end_month' => $data['is_ongoing'] ? null : ($data['end_month'] ?? null),
                'is_ongoing' => $data['is_ongoing'],
                'repository_visibility' => $data['repository_visibility'],
                'repository_url' => $data['repository_visibility'] === RepositoryVisibility::None->value
                    ? null
                    : ($data['repository_url'] ?? null),
                'show_repository' => $data['repository_visibility'] === RepositoryVisibility::Public->value
                    && $data['show_repository'],
                'demo_url' => $data['demo_url'] ?? null,
                'show_demo' => $data['show_demo'],
                'publication_status' => $data['publication_status'],
                'is_featured' => $data['is_featured'],
                'featured_order' => $data['is_featured'] ? $data['featured_order'] : null,
                'sort_order' => $data['sort_order'],
            ]);
            $project->published_at = $data['publication_status'] === PublicationStatus::Published->value
                ? ($project->published_at ?? now())
                : null;
            $projectChangedFields = array_values(array_diff(array_keys($project->getDirty()), ['updated_at']));
            $project->save();

            $translationsChanged = $this->syncTranslations($project, $data['translations']);
            $sectionsChanged = $this->syncSections($project, $data['sections']);
            $changedFields = [
                ...$projectChangedFields,
                ...($translationsChanged ? ['translations'] : []),
                ...($sectionsChanged ? ['case_study_sections'] : []),
            ];
            $changedFields = array_values(array_unique($changedFields));
            $changed = $changedFields !== [];

            if ($changed) {
                $this->auditRecorder->record(
                    actor: $actor,
                    action: $created ? 'project.created' : 'project.updated',
                    subjectType: 'project',
                    subjectId: $project->uuid,
                    changedFields: $changedFields,
                    requestId: $requestId,
                );
            }

            return $project;
        });

        if ($changed) {
            ProjectsCache::invalidate();
        }

        return $project;
    }

    /** @param array<string, array<string, mixed>|null> $translations */
    private function syncTranslations(Project $project, array $translations): bool
    {
        $changed = false;
        foreach (['fr', 'en'] as $locale) {
            $content = $translations[$locale] ?? null;
            $translation = $project->translations()->where('locale', $locale)->first();
            if (! is_array($content) || ! collect(['title', 'summary', 'role'])->contains(fn (string $field): bool => filled($content[$field] ?? null))) {
                if ($translation !== null) {
                    $translation->delete();
                    $changed = true;
                }

                continue;
            }

            $translation ??= $project->translations()->make(['locale' => $locale]);
            $translation->fill([
                'title' => (string) ($content['title'] ?? ''),
                'summary' => (string) ($content['summary'] ?? ''),
                'role' => (string) ($content['role'] ?? ''),
                'seo_title' => $content['seo_title'] ?: null,
                'seo_description' => $content['seo_description'] ?: null,
            ]);
            $changed = $changed || ! $translation->exists || $translation->isDirty();
            $translation->save();
        }

        return $changed;
    }

    /** @param list<array<string, mixed>> $sections */
    private function syncSections(Project $project, array $sections): bool
    {
        $changed = false;
        $keptIds = [];
        foreach ($sections as $row) {
            $section = isset($row['id'])
                ? $project->caseStudySections()->lockForUpdate()->findOrFail((int) $row['id'])
                : ($project->caseStudySections()->where('type', $row['type'])->lockForUpdate()->first()
                    ?? new CaseStudySection(['project_id' => $project->getKey()]));
            $section->fill([
                'type' => $row['type'],
                'is_public' => $row['is_public'],
                'is_verified' => $row['is_verified'],
                'sort_order' => $row['sort_order'],
            ]);
            $sectionChanged = ! $section->exists || $section->isDirty();
            $section->save();
            $keptIds[] = (int) $section->getKey();

            foreach (['fr', 'en'] as $locale) {
                $content = $row['translations'][$locale] ?? null;
                $translation = $section->translations()->where('locale', $locale)->first();
                if (! is_array($content) || (! filled($content['heading'] ?? null) && ! filled($content['body'] ?? null))) {
                    if ($translation !== null) {
                        $translation->delete();
                        $sectionChanged = true;
                    }

                    continue;
                }
                $translation ??= $section->translations()->make(['locale' => $locale]);
                $translation->fill([
                    'heading' => (string) ($content['heading'] ?? ''),
                    'body' => (string) ($content['body'] ?? ''),
                ]);
                $sectionChanged = $sectionChanged || ! $translation->exists || $translation->isDirty();
                $translation->save();
            }
            $changed = $changed || $sectionChanged;
        }

        $deletions = $project->caseStudySections()->when(
            $keptIds !== [],
            fn ($query) => $query->whereNotIn('id', $keptIds),
        )->delete();

        return $changed || $deletions > 0;
    }
}
