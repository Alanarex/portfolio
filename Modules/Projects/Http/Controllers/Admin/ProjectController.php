<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Projects\Application\DeleteProject;
use Modules\Projects\Application\ReorderProjects;
use Modules\Projects\Application\SaveProject;
use Modules\Projects\Enums\CaseStudySectionType;
use Modules\Projects\Enums\MediaKind;
use Modules\Projects\Enums\ProjectLifecycle;
use Modules\Projects\Enums\PublicationStatus;
use Modules\Projects\Enums\RepositoryVisibility;
use Modules\Projects\Http\Requests\ReorderProjectsRequest;
use Modules\Projects\Http\Requests\SaveProjectRequest;
use Modules\Projects\Models\CaseStudySection;
use Modules\Projects\Models\MediaAsset;
use Modules\Projects\Models\Project;

final class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Project::class);
        $status = $request->string('status')->toString();
        $featured = $request->string('featured')->toString();
        $query = Project::query()->with('translations')->orderBy('sort_order')->orderBy('slug');
        if (in_array($status, array_column(PublicationStatus::cases(), 'value'), true)) {
            $query->where('publication_status', $status);
        }
        if (in_array($featured, ['yes', 'no'], true)) {
            $query->where('is_featured', $featured === 'yes');
        }

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $query->get()->map(function (Project $project): array {
                $fr = $project->translations->firstWhere('locale', 'fr');

                return [
                    'id' => $project->id,
                    'slug' => $project->slug,
                    'title' => $fr?->title ?: $project->slug,
                    'lifecycle_status' => $project->lifecycle_status->value,
                    'publication_status' => $project->publication_status->value,
                    'is_featured' => $project->is_featured,
                    'featured_order' => $project->featured_order,
                    'sort_order' => $project->sort_order,
                    'deletion_pending' => $project->deletion_pending_at !== null,
                ];
            })->all(),
            'filters' => ['status' => $status, 'featured' => $featured],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Project::class);

        return Inertia::render('Admin/Projects/Edit', [
            'project' => $this->emptyProject(),
            'media' => [],
            'isNew' => true,
            'options' => $this->options(),
        ]);
    }

    public function store(SaveProjectRequest $request, SaveProject $saveProject): RedirectResponse
    {
        Gate::authorize('create', Project::class);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $project = $saveProject->execute(
            $request->validated(),
            $user,
            $request->attributes->get('request_id'),
        );

        return to_route('dashboard.projects.edit', $project)->with('success', 'Projet créé.');
    }

    public function edit(Project $project): Response
    {
        Gate::authorize('update', $project);
        $project->load(['translations', 'caseStudySections.translations', 'mediaAssets.translations']);

        return Inertia::render('Admin/Projects/Edit', [
            'project' => $this->serialize($project),
            'media' => $project->mediaAssets->sortBy('sort_order')->values()
                ->map(fn (MediaAsset $asset): array => $this->serializeMedia($asset))->all(),
            'isNew' => false,
            'options' => $this->options(),
        ]);
    }

    public function update(
        SaveProjectRequest $request,
        Project $project,
        SaveProject $saveProject,
    ): RedirectResponse {
        Gate::authorize('update', $project);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $saveProject->execute(
            $request->validated(),
            $user,
            $request->attributes->get('request_id'),
            $project,
        );

        return back()->with('success', 'Projet enregistré.');
    }

    public function preview(Project $project): Response
    {
        Gate::authorize('view', $project);
        $project->load(['translations', 'caseStudySections.translations', 'mediaAssets.translations']);

        return Inertia::render('Admin/Projects/Preview', [
            'project' => $this->serialize($project),
            'media' => $project->mediaAssets->sortBy('sort_order')->values()
                ->map(fn (MediaAsset $asset): array => $this->serializeMedia($asset))->all(),
        ]);
    }

    public function reorder(
        ReorderProjectsRequest $request,
        ReorderProjects $reorderProjects,
    ): RedirectResponse {
        Gate::authorize('update', new Project);
        $user = $request->user();
        abort_unless($user !== null, 403);
        /** @var list<int> $ids */
        $ids = $request->validated('ids');
        $reorderProjects->execute(
            $ids,
            $user,
            $request->attributes->get('request_id'),
        );

        return back()->with('success', 'Ordre des projets enregistré.');
    }

    public function destroy(Request $request, Project $project, DeleteProject $deleteProject): RedirectResponse
    {
        Gate::authorize('delete', $project);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $deleteProject->execute($project, $user, $request->attributes->get('request_id'));

        return to_route('dashboard.projects.index')->with('success', 'Projet supprimé.');
    }

    /** @return array<string, mixed> */
    private function emptyProject(): array
    {
        $headings = [
            'context' => ['fr' => 'Contexte', 'en' => 'Context'],
            'problem' => ['fr' => 'Problème', 'en' => 'Problem'],
            'approach' => ['fr' => 'Approche', 'en' => 'Approach'],
            'architecture' => ['fr' => 'Architecture', 'en' => 'Architecture'],
            'contribution' => ['fr' => 'Contribution', 'en' => 'Contribution'],
            'results' => ['fr' => 'Résultats', 'en' => 'Results'],
            'metrics' => ['fr' => 'Métriques', 'en' => 'Metrics'],
            'lessons' => ['fr' => 'Enseignements', 'en' => 'Lessons'],
        ];

        return [
            'slug' => '',
            'lifecycle_status' => ProjectLifecycle::Unspecified->value,
            'technologies' => [],
            'start_year' => null,
            'start_month' => null,
            'end_year' => null,
            'end_month' => null,
            'is_ongoing' => false,
            'repository_visibility' => RepositoryVisibility::None->value,
            'repository_url' => '',
            'show_repository' => false,
            'demo_url' => '',
            'show_demo' => false,
            'publication_status' => PublicationStatus::Draft->value,
            'is_featured' => false,
            'featured_order' => null,
            'sort_order' => (Project::query()->max('sort_order') ?? 0) + 10,
            'translations' => [
                'fr' => ['title' => '', 'summary' => '', 'role' => '', 'seo_title' => '', 'seo_description' => ''],
                'en' => ['title' => '', 'summary' => '', 'role' => '', 'seo_title' => '', 'seo_description' => ''],
            ],
            'sections' => collect(CaseStudySectionType::cases())->map(fn (CaseStudySectionType $type, int $index): array => [
                'type' => $type->value,
                'is_public' => false,
                'is_verified' => false,
                'sort_order' => ($index + 1) * 10,
                'translations' => [
                    'fr' => ['heading' => $headings[$type->value]['fr'], 'body' => ''],
                    'en' => ['heading' => $headings[$type->value]['en'], 'body' => ''],
                ],
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function serialize(Project $project): array
    {
        return [
            ...$project->only([
                'id', 'slug', 'technologies', 'start_year', 'start_month', 'end_year', 'end_month',
                'is_ongoing', 'repository_url', 'show_repository', 'demo_url', 'show_demo', 'is_featured',
                'featured_order', 'sort_order',
            ]),
            'lifecycle_status' => $project->lifecycle_status->value,
            'repository_visibility' => $project->repository_visibility->value,
            'publication_status' => $project->publication_status->value,
            'translations' => $this->translations(
                $project,
                ['title', 'summary', 'role', 'seo_title', 'seo_description'],
            ),
            'sections' => $project->caseStudySections->sortBy('sort_order')->values()
                ->map(fn (CaseStudySection $section): array => [
                    ...$section->only(['id', 'is_public', 'is_verified', 'sort_order']),
                    'type' => $section->type->value,
                    'translations' => $this->translations($section, ['heading', 'body']),
                ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeMedia(MediaAsset $asset): array
    {
        return [
            ...$asset->only([
                'id', 'uuid', 'project_id', 'mime_type', 'extension', 'size_bytes', 'width', 'height',
                'is_public', 'sort_order',
            ]),
            'kind' => $asset->kind->value,
            'original_filename' => $asset->original_filename,
            'deletion_pending' => $asset->deletion_pending_at !== null,
            'translations' => $this->translations($asset, ['alt_text', 'caption']),
        ];
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, array<string, mixed>>
     */
    private function translations(Project|CaseStudySection|MediaAsset $model, array $fields): array
    {
        return collect(['fr', 'en'])->mapWithKeys(function (string $locale) use ($model, $fields): array {
            $translation = $model->translations->firstWhere('locale', $locale);

            return [$locale => collect($fields)->mapWithKeys(
                fn (string $field): array => [$field => $translation?->{$field} ?? ''],
            )->all()];
        })->all();
    }

    /** @return array<string, list<string>> */
    private function options(): array
    {
        return [
            'publication_statuses' => array_column(PublicationStatus::cases(), 'value'),
            'lifecycle_statuses' => array_column(ProjectLifecycle::cases(), 'value'),
            'repository_visibilities' => array_column(RepositoryVisibility::cases(), 'value'),
            'media_kinds' => array_column(MediaKind::cases(), 'value'),
        ];
    }
}
