<?php

declare(strict_types=1);

namespace Modules\Career\Application;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Career\Enums\PublicationStatus;
use Modules\Career\Infrastructure\CareerCache;
use Modules\Career\Models\Achievement;
use Modules\Career\Models\Certification;
use Modules\Career\Models\EducationRecord;
use Modules\Career\Models\Experience;
use Modules\Career\Models\LanguageProficiency;

final class SaveCareerContent
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $requestId): void
    {
        $changed = DB::transaction(function () use ($data, $actor, $requestId): bool {
            $before = $this->fingerprint();
            $this->syncExperiences($data['experiences']);
            $this->syncCollection(
                EducationRecord::class,
                $data['education'],
                ['key', 'institution', 'start_year', 'start_month', 'end_year', 'end_month', 'status', 'sort_order'],
                'translations',
                ['program', 'level', 'location', 'summary'],
            );
            $this->syncCollection(
                Certification::class,
                $data['certifications'],
                ['key', 'issuer', 'credential_id', 'verification_url', 'issue_year', 'issue_month', 'result', 'is_verified', 'status', 'sort_order'],
                'translations',
                ['name', 'skill_label'],
            );
            $this->syncCollection(
                LanguageProficiency::class,
                $data['languages'],
                ['key', 'language_code', 'proficiency_kind', 'cefr_level', 'status', 'sort_order'],
                'translations',
                ['name', 'proficiency_label', 'evidence'],
            );
            $changed = $before !== $this->fingerprint();

            if ($changed) {
                $this->auditRecorder->record(
                    actor: $actor,
                    action: 'career.content.updated',
                    subjectType: 'career-content',
                    subjectId: 'portfolio',
                    changedFields: ['experiences', 'education', 'certifications', 'languages', 'ordering', 'publication'],
                    requestId: $requestId,
                );
            }

            return $changed;
        });

        if ($changed) {
            CareerCache::invalidate();
        }
    }

    /** @param list<array<string, mixed>> $rows */
    private function syncExperiences(array $rows): void
    {
        $keptIds = [];
        foreach ($rows as $row) {
            $experience = isset($row['id'])
                ? Experience::query()->lockForUpdate()->findOrFail((int) $row['id'])
                : (Experience::query()->where('key', $row['key'])->lockForUpdate()->first() ?? new Experience);
            $experience->fill($this->only($row, [
                'key', 'organization', 'start_year', 'start_month', 'end_year', 'end_month', 'is_current', 'status', 'sort_order',
            ]));
            if ($experience->is_current) {
                $experience->end_year = null;
                $experience->end_month = null;
            }
            $this->setPublishedAt($experience, (string) $row['status']);
            $experience->save();
            $keptIds[] = (int) $experience->getKey();
            $this->syncTranslations($experience, (array) $row['translations'], ['role', 'employment_type', 'location', 'summary', 'highlights']);

            $achievementIds = [];
            foreach ((array) $row['achievements'] as $achievementRow) {
                $achievement = isset($achievementRow['id'])
                    ? Achievement::query()->where('experience_id', $experience->getKey())->lockForUpdate()->findOrFail((int) $achievementRow['id'])
                    : (Achievement::query()
                        ->where('experience_id', $experience->getKey())
                        ->where('key', $achievementRow['key'])
                        ->lockForUpdate()->first() ?? new Achievement(['experience_id' => $experience->getKey()]));
                $achievement->fill($this->only($achievementRow, ['key', 'status', 'is_quantified', 'is_verified', 'sort_order']));
                $achievement->save();
                $achievementIds[] = (int) $achievement->getKey();
                $this->syncTranslations($achievement, (array) $achievementRow['translations'], ['statement']);
            }
            $experience->achievements()->when($achievementIds !== [], fn ($query) => $query->whereNotIn('id', $achievementIds))->delete();
            if ($achievementIds === []) {
                $experience->achievements()->delete();
            }
        }

        Experience::query()->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))->delete();
        if ($keptIds === []) {
            Experience::query()->delete();
        }
    }

    /**
     * @param  class-string<EducationRecord|Certification|LanguageProficiency>  $modelClass
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $fields
     * @param  list<string>  $translationFields
     */
    private function syncCollection(string $modelClass, array $rows, array $fields, string $relation, array $translationFields): void
    {
        $keptIds = [];
        foreach ($rows as $row) {
            $model = isset($row['id'])
                ? $modelClass::query()->lockForUpdate()->findOrFail((int) $row['id'])
                : ($modelClass::query()->where('key', $row['key'])->lockForUpdate()->first() ?? new $modelClass);
            $model->fill($this->only($row, $fields));
            $this->setPublishedAt($model, (string) $row['status']);
            $model->save();
            $keptIds[] = (int) $model->getKey();
            $this->syncTranslations($model, (array) $row[$relation], $translationFields);
        }

        $query = $modelClass::query();
        if ($keptIds !== []) {
            $query->whereNotIn('id', $keptIds);
        }
        $query->delete();
    }

    /**
     * @param  array<string, array<string, mixed>|null>  $translations
     * @param  list<string>  $fields
     */
    private function syncTranslations(Experience|Achievement|EducationRecord|Certification|LanguageProficiency $model, array $translations, array $fields): void
    {
        foreach (['fr', 'en'] as $locale) {
            $content = $translations[$locale] ?? null;
            $relation = $model->getRelationValue('translations') ?? $model->translations()->get();
            $translation = $relation->firstWhere('locale', $locale);
            $hasContent = is_array($content) && collect($fields)->contains(fn (string $field): bool => filled($content[$field] ?? null));

            if (! $hasContent) {
                $translation?->delete();

                continue;
            }

            $translation ??= $model->translations()->make(['locale' => $locale]);
            $values = $this->only($content, $fields);
            if (in_array('highlights', $fields, true)) {
                $values['highlights'] = array_values((array) ($content['highlights'] ?? []));
            }
            $translation->fill($values);
            $translation->save();
            $model->unsetRelation('translations');
        }
    }

    private function setPublishedAt(Model $model, string $status): void
    {
        if ($status === PublicationStatus::Published->value) {
            $model->setAttribute('published_at', $model->getAttribute('published_at') ?? now());
        } elseif (array_key_exists('published_at', $model->getAttributes())) {
            $model->setAttribute('published_at', null);
        }
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    private function only(array $source, array $keys): array
    {
        return array_intersect_key($source, array_flip($keys));
    }

    private function fingerprint(): string
    {
        $tables = [
            'career_experiences',
            'career_experience_translations',
            'career_achievements',
            'career_achievement_translations',
            'education_records',
            'education_record_translations',
            'certifications',
            'certification_translations',
            'language_proficiencies',
            'language_proficiency_translations',
        ];

        return hash('sha256', json_encode(collect($tables)->mapWithKeys(
            fn (string $table): array => [$table => DB::table($table)->orderBy('id')->get()->map(fn ($row): array => (array) $row)->all()]
        )->all(), JSON_THROW_ON_ERROR));
    }
}
