<?php

declare(strict_types=1);

namespace Modules\Career\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Modules\Career\Contracts\PublicCareerReader;
use Modules\Career\Data\PublicCareerData;
use Modules\Career\Enums\PublicationStatus;
use Modules\Career\Models\Certification;
use Modules\Career\Models\EducationRecord;
use Modules\Career\Models\Experience;
use Modules\Career\Models\LanguageProficiency;

final class DatabasePublicCareerReader implements PublicCareerReader
{
    public function forLocale(string $locale): ?PublicCareerData
    {
        if (! in_array($locale, ['fr', 'en'], true)) {
            return null;
        }

        return Cache::remember(CareerCache::key($locale), now()->addMinutes(5), function () use ($locale): PublicCareerData {
            $experiences = Experience::query()
                ->where('status', PublicationStatus::Published->value)
                ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
                ->with([
                    'translations' => fn ($query) => $query->where('locale', $locale),
                    'achievements' => fn ($query) => $query
                        ->where('status', PublicationStatus::Published->value)
                        ->where('is_verified', true)
                        ->orderBy('sort_order')
                        ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)]),
                ])
                ->orderBy('sort_order')
                ->orderByDesc('start_year')
                ->orderBy('key')
                ->get()
                ->map(function (Experience $experience): array {
                    $translation = $experience->translations->firstOrFail();

                    return [
                        'key' => $experience->key,
                        'organization' => $experience->organization,
                        'role' => $translation->role,
                        'employment_type' => $translation->employment_type,
                        'location' => $translation->location,
                        'summary' => $translation->summary,
                        'highlights' => $translation->highlights,
                        'start' => ['year' => $experience->start_year, 'month' => $experience->start_month],
                        'end' => $experience->is_current ? null : ['year' => $experience->end_year, 'month' => $experience->end_month],
                        'is_current' => $experience->is_current,
                        'achievements' => $experience->achievements
                            ->filter(fn ($achievement): bool => $achievement->translations->isNotEmpty())
                            ->map(function ($achievement): array {
                                $translation = $achievement->translations->firstOrFail();

                                return [
                                    'key' => $achievement->key,
                                    'statement' => $translation->statement,
                                    'is_quantified' => $achievement->is_quantified,
                                ];
                            })->values()->all(),
                    ];
                })->all();

            $education = EducationRecord::query()
                ->where('status', PublicationStatus::Published->value)
                ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
                ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
                ->orderBy('sort_order')->orderByDesc('start_year')->orderBy('key')->get()
                ->map(function (EducationRecord $record): array {
                    $translation = $record->translations->firstOrFail();

                    return [
                        'key' => $record->key,
                        'institution' => $record->institution,
                        'program' => $translation->program,
                        'level' => $translation->level,
                        'location' => $translation->location,
                        'summary' => $translation->summary,
                        'start' => ['year' => $record->start_year, 'month' => $record->start_month],
                        'end' => ['year' => $record->end_year, 'month' => $record->end_month],
                    ];
                })->all();

            $certifications = Certification::query()
                ->where('status', PublicationStatus::Published->value)
                ->where('is_verified', true)
                ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
                ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
                ->orderBy('sort_order')->orderByDesc('issue_year')->orderBy('key')->get()
                ->map(function (Certification $certification): array {
                    $translation = $certification->translations->firstOrFail();

                    return [
                        'key' => $certification->key,
                        'name' => $translation->name,
                        'issuer' => $certification->issuer,
                        'verification_url' => $certification->verification_url,
                        'issued' => ['year' => $certification->issue_year, 'month' => $certification->issue_month],
                        'result' => $certification->result,
                        'skill_label' => $translation->skill_label,
                    ];
                })->all();

            $languages = LanguageProficiency::query()
                ->where('status', PublicationStatus::Published->value)
                ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
                ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
                ->orderBy('sort_order')->orderBy('key')->get()
                ->map(function (LanguageProficiency $language): array {
                    $translation = $language->translations->firstOrFail();

                    return [
                        'key' => $language->key,
                        'language_code' => $language->language_code,
                        'name' => $translation->name,
                        'proficiency_kind' => $language->proficiency_kind,
                        'cefr_level' => $language->cefr_level,
                        'proficiency_label' => $translation->proficiency_label,
                        'evidence' => $translation->evidence,
                    ];
                })->all();

            return new PublicCareerData($experiences, $education, $certifications, $languages);
        });
    }
}
