<?php

declare(strict_types=1);

namespace Modules\Skills\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Modules\Skills\Contracts\PublicSkillsReader;
use Modules\Skills\Data\PublicSkillsData;
use Modules\Skills\Enums\PublicationStatus;
use Modules\Skills\Models\SkillCategory;

final class DatabasePublicSkillsReader implements PublicSkillsReader
{
    public function forLocale(string $locale): ?PublicSkillsData
    {
        if (! in_array($locale, ['fr', 'en'], true)) {
            return null;
        }

        return Cache::remember(SkillsCache::key($locale), now()->addMinutes(5), function () use ($locale): PublicSkillsData {
            $categories = SkillCategory::query()
                ->where('status', PublicationStatus::Published->value)
                ->where('is_visible', true)
                ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
                ->with([
                    'translations' => fn ($query) => $query->where('locale', $locale),
                    'skills' => fn ($query) => $query
                        ->where('status', PublicationStatus::Published->value)
                        ->where('is_visible', true)
                        ->whereHas('translations', fn ($translationQuery) => $translationQuery->where('locale', $locale))
                        ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                        ->orderBy('sort_order')->orderBy('key'),
                ])
                ->orderBy('sort_order')->orderBy('key')->get()
                ->map(fn (SkillCategory $category): array => $this->serializeCategory($category))
                ->all();

            return new PublicSkillsData($categories);
        });
    }

    /** @return array{key: string, name: string, skills: list<array{key: string, name: string, description: string|null}>} */
    private function serializeCategory(SkillCategory $category): array
    {
        $translation = $category->translations->firstOrFail();
        $skills = [];
        foreach ($category->skills as $skill) {
            $skillTranslation = $skill->translations->firstOrFail();
            $skills[] = [
                'key' => $skill->key,
                'name' => $skillTranslation->name,
                'description' => $skillTranslation->description,
            ];
        }

        return [
            'key' => $category->key,
            'name' => $translation->name,
            'skills' => $skills,
        ];
    }
}
