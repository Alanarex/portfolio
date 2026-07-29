<?php

declare(strict_types=1);

namespace Modules\Projects\Infrastructure;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Modules\Projects\Contracts\PublicProjectReader;
use Modules\Projects\Data\PublicProjectData;
use Modules\Projects\Enums\PublicationStatus;
use Modules\Projects\Enums\RepositoryVisibility;
use Modules\Projects\Models\Project;

final class DatabasePublicProjectReader implements PublicProjectReader
{
    public function all(string $locale): array
    {
        if (! $this->supports($locale)) {
            return [];
        }

        return Cache::remember(ProjectsCache::key($locale, 'all'), now()->addMinutes(5), fn (): array => $this
            ->query($locale)
            ->orderBy('sort_order')
            ->orderBy('slug')
            ->get()
            ->map(fn (Project $project): PublicProjectData => $this->map($project))
            ->all());
    }

    public function featured(string $locale): array
    {
        if (! $this->supports($locale)) {
            return [];
        }

        return Cache::remember(ProjectsCache::key($locale, 'featured'), now()->addMinutes(5), fn (): array => $this
            ->query($locale)
            ->where('is_featured', true)
            ->orderBy('featured_order')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Project $project): PublicProjectData => $this->map($project))
            ->all());
    }

    public function findBySlug(string $locale, string $slug): ?PublicProjectData
    {
        if (! $this->supports($locale)) {
            return null;
        }

        return Cache::remember(
            ProjectsCache::key($locale, 'slug:'.hash('sha256', $slug)),
            now()->addMinutes(5),
            function () use ($locale, $slug): ?PublicProjectData {
                $project = $this->query($locale)->where('slug', $slug)->first();

                return $project === null ? null : $this->map($project);
            },
        );
    }

    /** @return Builder<Project> */
    private function query(string $locale): Builder
    {
        return Project::query()
            ->where('publication_status', PublicationStatus::Published->value)
            ->whereNull('deletion_pending_at')
            ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
            ->with([
                'translations' => fn ($query) => $query->where('locale', $locale),
                'caseStudySections' => fn ($query) => $query
                    ->where('is_public', true)
                    ->where('is_verified', true)
                    ->whereHas('translations', fn ($translationQuery) => $translationQuery->where('locale', $locale))
                    ->orderBy('sort_order')
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)]),
                'mediaAssets' => fn ($query) => $query
                    ->where('is_public', true)
                    ->whereNull('deletion_pending_at')
                    ->whereHas('translations', fn ($translationQuery) => $translationQuery->where('locale', $locale))
                    ->orderBy('sort_order')
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)]),
            ]);
    }

    private function map(Project $project): PublicProjectData
    {
        $translation = $project->translations->firstOrFail();
        $repositoryUrl = $project->show_repository
            && $project->repository_visibility === RepositoryVisibility::Public
            ? $project->repository_url
            : null;

        return new PublicProjectData(
            slug: $project->slug,
            title: $translation->title,
            summary: $translation->summary,
            role: $translation->role,
            lifecycleStatus: $project->lifecycle_status->value,
            technologies: $project->technologies,
            dates: [
                'start' => ['year' => $project->start_year, 'month' => $project->start_month],
                'end' => $project->is_ongoing ? null : ['year' => $project->end_year, 'month' => $project->end_month],
                'ongoing' => $project->is_ongoing,
            ],
            featured: $project->is_featured,
            repositoryUrl: $repositoryUrl,
            demoUrl: $project->show_demo ? $project->demo_url : null,
            sections: $project->caseStudySections->map(function ($section): array {
                $translation = $section->translations->firstOrFail();

                return [
                    'type' => $section->type->value,
                    'heading' => $translation->heading,
                    'body' => $translation->body,
                ];
            })->all(),
            media: $project->mediaAssets->map(function ($asset): array {
                $translation = $asset->translations->firstOrFail();

                return array_filter([
                    'delivery_key' => $asset->uuid,
                    'kind' => $asset->kind->value,
                    'mime_type' => $asset->mime_type,
                    'size_bytes' => $asset->size_bytes,
                    'width' => $asset->width,
                    'height' => $asset->height,
                    'alt_text' => $translation->alt_text,
                    'caption' => $translation->caption,
                ], static fn (mixed $value): bool => $value !== null);
            })->all(),
        );
    }

    private function supports(string $locale): bool
    {
        return in_array($locale, ['fr', 'en'], true);
    }
}
