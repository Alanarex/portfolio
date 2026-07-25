<?php

declare(strict_types=1);

namespace Modules\Career\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Career\Enums\PublicationStatus;

final class UpdateCareerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $statuses = array_column(PublicationStatus::cases(), 'value');
        $translationRules = ['nullable', 'array'];

        return [
            'experiences' => ['required', 'array', 'max:50'],
            'experiences.*.id' => ['nullable', 'integer', 'exists:career_experiences,id', 'distinct'],
            'experiences.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'distinct'],
            'experiences.*.organization' => ['required', 'string', 'max:160'],
            'experiences.*.start_year' => ['required', 'integer', 'between:1950,2100'],
            'experiences.*.start_month' => ['nullable', 'integer', 'between:1,12'],
            'experiences.*.end_year' => ['nullable', 'integer', 'between:1950,2100'],
            'experiences.*.end_month' => ['nullable', 'integer', 'between:1,12'],
            'experiences.*.is_current' => ['required', 'boolean'],
            'experiences.*.status' => ['required', Rule::in($statuses)],
            'experiences.*.sort_order' => ['required', 'integer', 'between:0,1000000'],
            'experiences.*.translations' => ['required', 'array:fr,en'],
            'experiences.*.translations.fr' => $translationRules,
            'experiences.*.translations.en' => $translationRules,
            'experiences.*.translations.*.role' => ['nullable', 'string', 'max:180'],
            'experiences.*.translations.*.employment_type' => ['nullable', 'string', 'max:100'],
            'experiences.*.translations.*.location' => ['nullable', 'string', 'max:160'],
            'experiences.*.translations.*.summary' => ['nullable', 'string', 'max:5000'],
            'experiences.*.translations.*.highlights' => ['nullable', 'array', 'max:30'],
            'experiences.*.translations.*.highlights.*' => ['required', 'string', 'max:500'],
            'experiences.*.achievements' => ['required', 'array', 'max:20'],
            'experiences.*.achievements.*.id' => ['nullable', 'integer', 'exists:career_achievements,id', 'distinct'],
            'experiences.*.achievements.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'experiences.*.achievements.*.status' => ['required', Rule::in($statuses)],
            'experiences.*.achievements.*.is_quantified' => ['required', 'boolean'],
            'experiences.*.achievements.*.is_verified' => ['required', 'boolean'],
            'experiences.*.achievements.*.sort_order' => ['required', 'integer', 'between:0,1000000'],
            'experiences.*.achievements.*.translations' => ['required', 'array:fr,en'],
            'experiences.*.achievements.*.translations.fr' => $translationRules,
            'experiences.*.achievements.*.translations.en' => $translationRules,
            'experiences.*.achievements.*.translations.*.statement' => ['nullable', 'string', 'max:2000'],

            'education' => ['required', 'array', 'max:30'],
            'education.*.id' => ['nullable', 'integer', 'exists:education_records,id', 'distinct'],
            'education.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'distinct'],
            'education.*.institution' => ['required', 'string', 'max:160'],
            'education.*.start_year' => ['required', 'integer', 'between:1950,2100'],
            'education.*.start_month' => ['nullable', 'integer', 'between:1,12'],
            'education.*.end_year' => ['nullable', 'integer', 'between:1950,2100'],
            'education.*.end_month' => ['nullable', 'integer', 'between:1,12'],
            'education.*.status' => ['required', Rule::in($statuses)],
            'education.*.sort_order' => ['required', 'integer', 'between:0,1000000'],
            'education.*.translations' => ['required', 'array:fr,en'],
            'education.*.translations.fr' => $translationRules,
            'education.*.translations.en' => $translationRules,
            'education.*.translations.*.program' => ['nullable', 'string', 'max:200'],
            'education.*.translations.*.level' => ['nullable', 'string', 'max:160'],
            'education.*.translations.*.location' => ['nullable', 'string', 'max:160'],
            'education.*.translations.*.summary' => ['nullable', 'string', 'max:5000'],

            'certifications' => ['required', 'array', 'max:50'],
            'certifications.*.id' => ['nullable', 'integer', 'exists:certifications,id', 'distinct'],
            'certifications.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'distinct'],
            'certifications.*.issuer' => ['nullable', 'string', 'max:160'],
            'certifications.*.credential_id' => ['nullable', 'string', 'max:160'],
            'certifications.*.verification_url' => ['nullable', 'url:http,https', 'max:2048'],
            'certifications.*.issue_year' => ['required', 'integer', 'between:1950,2100'],
            'certifications.*.issue_month' => ['nullable', 'integer', 'between:1,12'],
            'certifications.*.result' => ['nullable', 'string', 'max:120'],
            'certifications.*.is_verified' => ['required', 'boolean'],
            'certifications.*.status' => ['required', Rule::in($statuses)],
            'certifications.*.sort_order' => ['required', 'integer', 'between:0,1000000'],
            'certifications.*.translations' => ['required', 'array:fr,en'],
            'certifications.*.translations.fr' => $translationRules,
            'certifications.*.translations.en' => $translationRules,
            'certifications.*.translations.*.name' => ['nullable', 'string', 'max:180'],
            'certifications.*.translations.*.skill_label' => ['nullable', 'string', 'max:180'],

            'languages' => ['required', 'array', 'max:30'],
            'languages.*.id' => ['nullable', 'integer', 'exists:language_proficiencies,id', 'distinct'],
            'languages.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'distinct'],
            'languages.*.language_code' => ['required', 'string', 'max:10', 'distinct'],
            'languages.*.proficiency_kind' => ['required', Rule::in(['native', 'bilingual', 'cefr'])],
            'languages.*.cefr_level' => ['nullable', Rule::in(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])],
            'languages.*.status' => ['required', Rule::in($statuses)],
            'languages.*.sort_order' => ['required', 'integer', 'between:0,1000000'],
            'languages.*.translations' => ['required', 'array:fr,en'],
            'languages.*.translations.fr' => $translationRules,
            'languages.*.translations.en' => $translationRules,
            'languages.*.translations.*.name' => ['nullable', 'string', 'max:100'],
            'languages.*.translations.*.proficiency_label' => ['nullable', 'string', 'max:160'],
            'languages.*.translations.*.evidence' => ['nullable', 'string', 'max:240'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach (['experiences', 'education', 'certifications', 'languages'] as $collection) {
                foreach ((array) $this->input($collection, []) as $index => $record) {
                    if (! is_array($record)) {
                        continue;
                    }

                    if (($record['status'] ?? null) === PublicationStatus::Published->value) {
                        foreach (['fr', 'en'] as $locale) {
                            $translation = $record['translations'][$locale] ?? null;
                            if (! is_array($translation) || ! $this->hasRequiredTranslation($collection, $translation)) {
                                $validator->errors()->add("{$collection}.{$index}.translations.{$locale}", 'Une traduction complète FR et EN est requise avant publication.');
                            }
                        }
                        if ($collection === 'certifications' && ($record['is_verified'] ?? false) !== true) {
                            $validator->errors()->add("{$collection}.{$index}.is_verified", 'Une certification doit être vérifiée avant publication.');
                        }
                    }

                    $this->validateDateOrder($validator, $collection, (int) $index, $record);
                }
            }

            foreach ((array) $this->input('experiences', []) as $experienceIndex => $experience) {
                if (! is_array($experience)) {
                    continue;
                }
                foreach ((array) ($experience['achievements'] ?? []) as $achievementIndex => $achievement) {
                    if (! is_array($achievement)) {
                        continue;
                    }
                    if (($achievement['status'] ?? null) === PublicationStatus::Published->value && ($achievement['is_verified'] ?? false) !== true) {
                        $validator->errors()->add("experiences.{$experienceIndex}.achievements.{$achievementIndex}.is_verified", 'Un résultat non vérifié ne peut pas être publié.');
                    }
                    if (($achievement['status'] ?? null) === PublicationStatus::Published->value) {
                        foreach (['fr', 'en'] as $locale) {
                            if (! filled($achievement['translations'][$locale]['statement'] ?? null)) {
                                $validator->errors()->add("experiences.{$experienceIndex}.achievements.{$achievementIndex}.translations.{$locale}.statement", 'Une traduction complète FR et EN est requise avant publication.');
                            }
                        }
                    }
                }
            }
        }];
    }

    /** @param array<string, mixed> $translation */
    private function hasRequiredTranslation(string $collection, array $translation): bool
    {
        return match ($collection) {
            'experiences' => filled($translation['role'] ?? null),
            'education' => filled($translation['program'] ?? null),
            'certifications' => filled($translation['name'] ?? null),
            'languages' => filled($translation['name'] ?? null) && filled($translation['proficiency_label'] ?? null),
            default => false,
        };
    }

    /** @param array<string, mixed> $record */
    private function validateDateOrder(Validator $validator, string $collection, int $index, array $record): void
    {
        $startYear = $record['start_year'] ?? null;
        $endYear = $record['end_year'] ?? null;
        if (! is_numeric($startYear) || ! is_numeric($endYear)) {
            return;
        }

        $start = ((int) $startYear * 12) + (int) ($record['start_month'] ?? 0);
        $end = ((int) $endYear * 12) + (int) ($record['end_month'] ?? 12);
        if ($end < $start) {
            $validator->errors()->add("{$collection}.{$index}.end_year", 'La fin doit être postérieure au début.');
        }
    }
}
