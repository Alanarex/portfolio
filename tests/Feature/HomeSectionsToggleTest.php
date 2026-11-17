<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Settings\Contracts\PublicSettingsReader;
use Modules\Settings\Data\HomeSections;
use Modules\Settings\Data\PublicSettingsData;
use Modules\Settings\Models\PublicFeatureFlag;
use Modules\Settings\Models\SiteSetting;
use Tests\TestCase;

final class HomeSectionsToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_section_is_visible_unless_a_flag_disables_it(): void
    {
        $settings = new PublicSettingsData('Site', 'fr', null, null, [], ['section_interets' => false]);

        foreach (HomeSections::ids() as $id) {
            self::assertSame($id !== 'interets', $settings->sectionVisible($id), $id);
        }
    }

    public function test_the_dashboard_lists_and_saves_every_section_toggle(): void
    {
        $administrator = User::factory()->administrator()->create();
        $session = ['auth_version' => $administrator->auth_version];

        $this->actingAs($administrator)->withSession($session)
            ->get('/dashboard/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Edit')
                ->where('settings.sections.contact', true)
                ->has('settings.sections', count(HomeSections::ids()))
                ->where('sectionLabels.interets', 'En dehors du code'));

        $payload = $this->payload();
        $payload['sections'] = array_fill_keys(HomeSections::ids(), true);
        $payload['sections']['formation'] = false;

        $this->actingAs($administrator)->withSession($session)
            ->put('/dashboard/settings', $payload)
            ->assertSessionHasNoErrors();

        $settingsId = SiteSetting::query()->where('key', 'main')->value('id');
        self::assertFalse((bool) PublicFeatureFlag::query()->where('site_setting_id', $settingsId)->where('key', 'section_formation')->value('enabled'));
        self::assertTrue((bool) PublicFeatureFlag::query()->where('site_setting_id', $settingsId)->where('key', 'section_contact')->value('enabled'));
        self::assertFalse($this->app->make(PublicSettingsReader::class)->forLocale('fr')?->sectionVisible('formation') ?? true);

        $payload['sections']['unknown'] = true;
        $this->actingAs($administrator)->withSession($session)
            ->put('/dashboard/settings', $payload)
            ->assertSessionHasErrors('sections');
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'site_name' => 'Portfolio',
            'default_locale' => 'fr',
            'contact_email' => '',
            'contact_phone' => '',
            'show_email' => false,
            'show_phone' => false,
            'social_links' => [['platform' => 'github', 'url' => 'https://github.com/example', 'is_enabled' => false, 'is_public' => false, 'sort_order' => 0]],
            'feature_flags' => ['projects' => true, 'contact' => false, 'cv' => false, 'activity' => false, 'three_d' => false],
        ];
    }
}
