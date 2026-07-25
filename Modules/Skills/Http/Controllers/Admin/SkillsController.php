<?php

declare(strict_types=1);

namespace Modules\Skills\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Skills\Application\SaveSkillsContent;
use Modules\Skills\Http\Requests\UpdateSkillsRequest;
use Modules\Skills\Models\Skill;
use Modules\Skills\Models\SkillCategory;

final class SkillsController extends Controller
{
    public function edit(): Response
    {
        Gate::authorize('viewAny', SkillCategory::class);

        return Inertia::render('Admin/Skills/Edit', [
            'categories' => SkillCategory::query()->with(['translations', 'skills.translations'])->orderBy('sort_order')->get()
                ->map(fn (SkillCategory $category): array => [
                    ...$category->only(['id', 'key', 'is_visible', 'sort_order']),
                    'status' => $category->status->value,
                    'translations' => $this->translations($category, false),
                    'skills' => $category->skills->sortBy('sort_order')->values()->map(fn (Skill $skill): array => [
                        ...$skill->only(['id', 'key', 'is_visible', 'sort_order']),
                        'status' => $skill->status->value,
                        'translations' => $this->translations($skill, true),
                    ])->all(),
                ])->all(),
        ]);
    }

    public function update(UpdateSkillsRequest $request, SaveSkillsContent $action): RedirectResponse
    {
        Gate::authorize('update', new SkillCategory);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $action->execute($request->validated(), $user, $request->attributes->get('request_id'));

        return back()->with('success', 'Compétences enregistrées.');
    }

    /** @return array<string, array<string, string>> */
    private function translations(SkillCategory|Skill $model, bool $withDescription): array
    {
        return collect(['fr', 'en'])->mapWithKeys(function (string $locale) use ($model, $withDescription): array {
            $translation = $model->translations->firstWhere('locale', $locale);
            $content = ['name' => $translation === null ? '' : $translation->name];
            if ($withDescription) {
                $content['description'] = $translation === null ? '' : ($translation->description ?? '');
            }

            return [$locale => $content];
        })->all();
    }
}
