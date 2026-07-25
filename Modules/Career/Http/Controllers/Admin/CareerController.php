<?php

declare(strict_types=1);

namespace Modules\Career\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Career\Application\SaveCareerContent;
use Modules\Career\Http\Requests\UpdateCareerRequest;
use Modules\Career\Models\Achievement;
use Modules\Career\Models\Certification;
use Modules\Career\Models\EducationRecord;
use Modules\Career\Models\Experience;
use Modules\Career\Models\LanguageProficiency;

final class CareerController extends Controller
{
    public function edit(): Response
    {
        Gate::authorize('viewAny', Experience::class);

        return Inertia::render('Admin/Career/Edit', [
            'career' => [
                'experiences' => Experience::query()->with(['translations', 'achievements.translations'])->orderBy('sort_order')->get()->map(fn (Experience $record): array => [
                    ...$record->only(['id', 'key', 'organization', 'start_year', 'start_month', 'end_year', 'end_month', 'is_current', 'sort_order']),
                    'status' => $record->status->value,
                    'translations' => $this->translations($record, ['role', 'employment_type', 'location', 'summary', 'highlights']),
                    'achievements' => $record->achievements->sortBy('sort_order')->values()->map(fn (Achievement $achievement): array => [
                        ...$achievement->only(['id', 'key', 'is_quantified', 'is_verified', 'sort_order']),
                        'status' => $achievement->status->value,
                        'translations' => $this->translations($achievement, ['statement']),
                    ])->all(),
                ])->all(),
                'education' => EducationRecord::query()->with('translations')->orderBy('sort_order')->get()->map(fn (EducationRecord $record): array => [
                    ...$record->only(['id', 'key', 'institution', 'start_year', 'start_month', 'end_year', 'end_month', 'sort_order']),
                    'status' => $record->status->value,
                    'translations' => $this->translations($record, ['program', 'level', 'location', 'summary']),
                ])->all(),
                'certifications' => Certification::query()->with('translations')->orderBy('sort_order')->get()->map(fn (Certification $record): array => [
                    ...$record->only(['id', 'key', 'issuer', 'credential_id', 'verification_url', 'issue_year', 'issue_month', 'result', 'is_verified', 'sort_order']),
                    'status' => $record->status->value,
                    'translations' => $this->translations($record, ['name', 'skill_label']),
                ])->all(),
                'languages' => LanguageProficiency::query()->with('translations')->orderBy('sort_order')->get()->map(fn (LanguageProficiency $record): array => [
                    ...$record->only(['id', 'key', 'language_code', 'proficiency_kind', 'cefr_level', 'sort_order']),
                    'status' => $record->status->value,
                    'translations' => $this->translations($record, ['name', 'proficiency_label', 'evidence']),
                ])->all(),
            ],
        ]);
    }

    public function update(UpdateCareerRequest $request, SaveCareerContent $action): RedirectResponse
    {
        Gate::authorize('update', new Experience);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $action->execute($request->validated(), $user, $request->attributes->get('request_id'));

        return back()->with('success', 'Parcours professionnel enregistré.');
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, array<string, mixed>>
     */
    private function translations(Experience|Achievement|EducationRecord|Certification|LanguageProficiency $model, array $fields): array
    {
        return collect(['fr', 'en'])->mapWithKeys(function (string $locale) use ($model, $fields): array {
            $translation = $model->translations->firstWhere('locale', $locale);

            return [$locale => collect($fields)->mapWithKeys(function (string $field) use ($translation): array {
                $default = $field === 'highlights' ? [] : '';

                return [$field => $translation === null ? $default : $translation->{$field}];
            })->all()];
        })->all();
    }
}
