<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Career\Models\Experience;
use Modules\Profile\Models\Profile;
use Modules\Projects\Models\Project;
use Modules\Skills\Models\Skill;
use Tests\TestCase;

final class EnglishContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_item_gets_an_english_translation_and_stays_a_draft(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (Skill::query()->with('translations')->get() as $skill) {
            $this->assertNotNull($skill->translations->firstWhere('locale', 'en'), "skill {$skill->key} has no EN");
        }
        foreach (Project::query()->with('translations')->get() as $project) {
            $this->assertNotNull($project->translations->firstWhere('locale', 'en'), "project {$project->slug} has no EN");
            $this->assertSame('draft', $project->publication_status->value);
        }
        foreach (Experience::query()->with('translations')->get() as $experience) {
            $this->assertNotNull($experience->translations->firstWhere('locale', 'en'));
            $this->assertSame('draft', $experience->status->value);
        }

        $profile = Profile::query()->where('key', 'main')->firstOrFail();
        $this->assertFalse($profile->is_published);
        $this->assertSame(['en', 'fr'], $profile->translations->pluck('locale')->sort()->values()->all());
    }

    public function test_seeding_twice_changes_nothing_and_keeps_dashboard_edits(): void
    {
        $this->seed(DatabaseSeeder::class);
        Profile::query()->where('key', 'main')->update(['display_name' => 'Edited']);
        $count = Skill::query()->count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($count, Skill::query()->count());
        $this->assertSame('Edited', Profile::query()->where('key', 'main')->value('display_name'));
    }
}
