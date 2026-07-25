<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\ActivityLog\Models\AuditEvent;
use Modules\Career\Contracts\PublicCareerReader;
use Modules\Skills\Contracts\PublicSkillsReader;
use Tests\TestCase;

final class CareerSkillsCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_career_and_skills_routes_require_an_administrator(): void
    {
        $this->get('/dashboard/career')->assertRedirect('/login');
        $this->get('/dashboard/skills')->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth_version' => $user->auth_version])
            ->get('/dashboard/career')->assertForbidden();
        $this->actingAs($user)->withSession(['auth_version' => $user->auth_version])
            ->put('/dashboard/skills', ['categories' => []])->assertForbidden();

        $administrator = User::factory()->administrator()->create();
        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->get('/dashboard/career')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Career/Edit')->has('career.experiences', 0));
        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->get('/dashboard/skills')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Skills/Edit')->has('categories', 0));
    }

    public function test_verified_reference_seeding_is_idempotent_draft_only_and_exact(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('career_experiences', 2);
        $this->assertDatabaseCount('education_records', 2);
        $this->assertDatabaseCount('certifications', 1);
        $this->assertDatabaseCount('language_proficiencies', 3);
        $this->assertDatabaseCount('skill_categories', 7);
        $this->assertDatabaseCount('skills', 59);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('career_experiences', ['status' => 'published']);
        $this->assertDatabaseMissing('skill_categories', ['status' => 'published']);
        $this->assertDatabaseHas('certifications', [
            'key' => 'toeic-2026',
            'issuer' => null,
            'credential_id' => null,
            'verification_url' => null,
            'result' => '940',
        ]);
        $this->assertDatabaseHas('career_achievements', [
            'key' => 'catalogue-sql-performance',
            'is_quantified' => true,
            'is_verified' => true,
        ]);
        self::assertStringNotContainsString('Handicapacité', json_encode([
            ...\DB::table('career_achievement_translations')->pluck('statement')->all(),
            ...\DB::table('certification_translations')->pluck('name')->all(),
        ], JSON_THROW_ON_ERROR));
        self::assertSame([], $this->app->make(PublicCareerReader::class)->forLocale('fr')?->toArray()['experiences']);
        self::assertSame([], $this->app->make(PublicSkillsReader::class)->forLocale('fr')?->toArray()['categories']);
    }

    public function test_administrator_can_manage_ordered_published_career_content(): void
    {
        $administrator = User::factory()->administrator()->create();
        $payload = $this->careerPayload();

        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/career', $payload)->assertSessionHasNoErrors()->assertRedirect();

        $public = $this->app->make(PublicCareerReader::class)->forLocale('en')?->toArray();
        self::assertSame('Example role', $public['experiences'][0]['role'] ?? null);
        self::assertSame('More than 385 queries reduced to 6.', $public['experiences'][0]['achievements'][0]['statement'] ?? null);
        self::assertSame('Example program', $public['education'][0]['program'] ?? null);
        self::assertSame('TOEIC', $public['certifications'][0]['name'] ?? null);
        self::assertSame('English', $public['languages'][0]['name'] ?? null);
        self::assertArrayNotHasKey('source_reference', $public['experiences'][0]['achievements'][0] ?? []);

        $payload['experiences'][0]['translations']['en']['role'] = 'Updated role';
        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/career', $payload)->assertSessionHasNoErrors();
        self::assertSame('Updated role', $this->app->make(PublicCareerReader::class)->forLocale('en')?->toArray()['experiences'][0]['role']);

        $audit = AuditEvent::query()->where('subject_type', 'career-content')->get();
        self::assertCount(2, $audit);
        self::assertStringNotContainsString('Updated role', $audit->toJson());

        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/career', $payload)->assertSessionHasNoErrors();
        self::assertCount(2, AuditEvent::query()->where('subject_type', 'career-content')->get());
    }

    public function test_unverified_metrics_incomplete_publications_dates_and_unsafe_urls_are_rejected_atomically(): void
    {
        $administrator = User::factory()->administrator()->create();
        $payload = $this->careerPayload();
        $payload['experiences'][0]['achievements'][0]['is_verified'] = false;
        $payload['education'][0]['end_year'] = 2020;
        $payload['certifications'][0]['verification_url'] = 'javascript:alert(1)';
        $payload['languages'][0]['translations']['en']['name'] = '';

        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/career', $payload)
            ->assertSessionHasErrors([
                'experiences.0.achievements.0.is_verified',
                'education.0.end_year',
                'certifications.0.verification_url',
                'languages.0.translations.en',
            ]);

        $this->assertDatabaseCount('career_experiences', 0);
        $this->assertDatabaseCount('audit_events', 0);
    }

    public function test_skills_crud_ordering_visibility_and_parent_publication_filtering_work(): void
    {
        $administrator = User::factory()->administrator()->create();
        $payload = $this->skillsPayload();

        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/skills', $payload)->assertSessionHasNoErrors();

        $public = $this->app->make(PublicSkillsReader::class)->forLocale('en')?->toArray();
        self::assertSame(['laravel', 'php'], array_column($public['categories'][0]['skills'] ?? [], 'key'));
        self::assertArrayNotHasKey('status', $public['categories'][0] ?? []);

        $payload['categories'][0]['is_visible'] = false;
        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/skills', $payload)->assertSessionHasNoErrors();
        self::assertSame([], $this->app->make(PublicSkillsReader::class)->forLocale('en')?->toArray()['categories']);
        self::assertStringNotContainsString('Laravel', AuditEvent::query()->get()->toJson());
    }

    public function test_duplicate_skill_keys_and_missing_english_public_copy_are_rejected(): void
    {
        $administrator = User::factory()->administrator()->create();
        $payload = $this->skillsPayload();
        $payload['categories'][0]['skills'][1]['key'] = 'laravel';
        $payload['categories'][0]['translations']['en']['name'] = '';

        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/skills', $payload)
            ->assertSessionHasErrors([
                'categories.0.skills.1.key',
                'categories.0.translations.en.name',
            ]);
        $this->assertDatabaseCount('skills', 0);
    }

    /** @return array<string, mixed> */
    private function careerPayload(): array
    {
        return [
            'experiences' => [[
                'key' => 'example-role', 'organization' => 'Example', 'start_year' => 2024, 'start_month' => 9,
                'end_year' => null, 'end_month' => null, 'is_current' => true, 'status' => 'published', 'sort_order' => 10,
                'translations' => [
                    'fr' => ['role' => 'Rôle exemple', 'employment_type' => 'Alternance', 'location' => 'France', 'summary' => 'Résumé.', 'highlights' => ['Point vérifié']],
                    'en' => ['role' => 'Example role', 'employment_type' => 'Apprenticeship', 'location' => 'France', 'summary' => 'Summary.', 'highlights' => ['Verified point']],
                ],
                'achievements' => [[
                    'key' => 'verified-result', 'status' => 'published', 'is_quantified' => true, 'is_verified' => true, 'sort_order' => 10,
                    'translations' => [
                        'fr' => ['statement' => 'Plus de 385 requêtes réduites à 6.'],
                        'en' => ['statement' => 'More than 385 queries reduced to 6.'],
                    ],
                ]],
            ]],
            'education' => [[
                'key' => 'example-program', 'institution' => 'Example School', 'start_year' => 2023, 'start_month' => null,
                'end_year' => 2024, 'end_month' => null, 'status' => 'published', 'sort_order' => 10,
                'translations' => [
                    'fr' => ['program' => 'Programme exemple', 'level' => 'Niveau 7', 'location' => 'Angers', 'summary' => 'Résumé.'],
                    'en' => ['program' => 'Example program', 'level' => 'Level 7', 'location' => 'Angers', 'summary' => 'Summary.'],
                ],
            ]],
            'certifications' => [[
                'key' => 'toeic', 'issuer' => '', 'credential_id' => '', 'verification_url' => '', 'issue_year' => 2026,
                'issue_month' => 5, 'result' => '940', 'is_verified' => true, 'status' => 'published', 'sort_order' => 10,
                'translations' => ['fr' => ['name' => 'TOEIC', 'skill_label' => 'Anglais professionnel'], 'en' => ['name' => 'TOEIC', 'skill_label' => 'Professional English']],
            ]],
            'languages' => [[
                'key' => 'english', 'language_code' => 'en', 'proficiency_kind' => 'cefr', 'cefr_level' => 'B2',
                'status' => 'published', 'sort_order' => 10,
                'translations' => ['fr' => ['name' => 'Anglais', 'proficiency_label' => 'B2', 'evidence' => 'TOEIC 940'], 'en' => ['name' => 'English', 'proficiency_label' => 'B2', 'evidence' => 'TOEIC 940']],
            ]],
        ];
    }

    /** @return array<string, mixed> */
    private function skillsPayload(): array
    {
        return ['categories' => [[
            'key' => 'backend', 'status' => 'published', 'is_visible' => true, 'sort_order' => 10,
            'translations' => ['fr' => ['name' => 'Backend'], 'en' => ['name' => 'Backend']],
            'skills' => [
                ['key' => 'laravel', 'status' => 'published', 'is_visible' => true, 'sort_order' => 10, 'translations' => ['fr' => ['name' => 'Laravel', 'description' => ''], 'en' => ['name' => 'Laravel', 'description' => '']]],
                ['key' => 'php', 'status' => 'published', 'is_visible' => true, 'sort_order' => 20, 'translations' => ['fr' => ['name' => 'PHP', 'description' => ''], 'en' => ['name' => 'PHP', 'description' => '']]],
            ],
        ]]];
    }
}
