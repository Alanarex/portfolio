<?php

declare(strict_types=1);

namespace Modules\Skills\Application;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Skills\Enums\PublicationStatus;
use Modules\Skills\Infrastructure\SkillsCache;
use Modules\Skills\Models\Skill;
use Modules\Skills\Models\SkillCategory;

final class SaveSkillsContent
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $requestId): void
    {
        $changed = DB::transaction(function () use ($data, $actor, $requestId): bool {
            $before = $this->fingerprint();
            $categoryIds = [];

            foreach ($data['categories'] as $categoryRow) {
                $category = isset($categoryRow['id'])
                    ? SkillCategory::query()->lockForUpdate()->findOrFail((int) $categoryRow['id'])
                    : (SkillCategory::query()->where('key', $categoryRow['key'])->lockForUpdate()->first() ?? new SkillCategory);
                $category->fill($this->only($categoryRow, ['key', 'status', 'is_visible', 'sort_order']));
                $this->setPublishedAt($category, (string) $categoryRow['status']);
                $category->save();
                $categoryIds[] = (int) $category->getKey();
                $this->syncTranslations($category, (array) $categoryRow['translations']);

                $skillIds = [];
                foreach ((array) $categoryRow['skills'] as $skillRow) {
                    $skill = isset($skillRow['id'])
                        ? Skill::query()->where('skill_category_id', $category->getKey())->lockForUpdate()->findOrFail((int) $skillRow['id'])
                        : (Skill::query()->where('key', $skillRow['key'])->lockForUpdate()->first() ?? new Skill(['skill_category_id' => $category->getKey()]));
                    $skill->skill_category_id = $category->getKey();
                    $skill->fill($this->only($skillRow, ['key', 'status', 'is_visible', 'sort_order']));
                    $this->setPublishedAt($skill, (string) $skillRow['status']);
                    $skill->save();
                    $skillIds[] = (int) $skill->getKey();
                    $this->syncTranslations($skill, (array) $skillRow['translations']);
                }
                $category->skills()->when($skillIds !== [], fn ($query) => $query->whereNotIn('id', $skillIds))->delete();
                if ($skillIds === []) {
                    $category->skills()->delete();
                }
            }

            $removedCategoryIds = SkillCategory::query()
                ->when($categoryIds !== [], fn ($query) => $query->whereNotIn('id', $categoryIds))
                ->pluck('id');
            if ($removedCategoryIds->isNotEmpty()) {
                Skill::query()->whereIn('skill_category_id', $removedCategoryIds)->delete();
            }
            SkillCategory::query()->whereIn('id', $removedCategoryIds)->delete();
            if ($categoryIds === []) {
                Skill::query()->delete();
                SkillCategory::query()->delete();
            }

            $changed = $before !== $this->fingerprint();
            if ($changed) {
                $this->auditRecorder->record(
                    actor: $actor,
                    action: 'skills.content.updated',
                    subjectType: 'skills-content',
                    subjectId: 'portfolio',
                    changedFields: ['categories', 'skills', 'ordering', 'publication', 'visibility'],
                    requestId: $requestId,
                );
            }

            return $changed;
        });

        if ($changed) {
            SkillsCache::invalidate();
        }
    }

    /** @param array<string, array<string, mixed>|null> $translations */
    private function syncTranslations(SkillCategory|Skill $model, array $translations): void
    {
        foreach (['fr', 'en'] as $locale) {
            $content = $translations[$locale] ?? null;
            $translation = $model->translations()->where('locale', $locale)->first();
            if (! is_array($content) || ! filled($content['name'] ?? null)) {
                $translation?->delete();

                continue;
            }
            $translation ??= $model->translations()->make(['locale' => $locale]);
            $translation->fill(array_filter([
                'name' => $content['name'],
                'description' => $content['description'] ?? null,
            ], fn (mixed $_value, string $key): bool => $key === 'name' || $model instanceof Skill, ARRAY_FILTER_USE_BOTH));
            $translation->save();
        }
    }

    private function setPublishedAt(Model $model, string $status): void
    {
        $model->setAttribute('published_at', $status === PublicationStatus::Published->value
            ? ($model->getAttribute('published_at') ?? now())
            : null);
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
        $tables = ['skill_categories', 'skill_category_translations', 'skills', 'skill_translations'];

        return hash('sha256', json_encode(collect($tables)->mapWithKeys(
            fn (string $table): array => [$table => DB::table($table)->orderBy('id')->get()->map(fn ($row): array => (array) $row)->all()]
        )->all(), JSON_THROW_ON_ERROR));
    }
}
