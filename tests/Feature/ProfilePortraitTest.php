<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\ActivityLog\Models\AuditEvent;
use Modules\Profile\Infrastructure\PublishedPortrait;
use Modules\Profile\Models\Profile;
use Tests\TestCase;

final class ProfilePortraitTest extends TestCase
{
    use RefreshDatabase;

    /** PNG 1 × 1 valide, sans dépendre de GD pour le générer. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function test_portrait_routes_require_an_administrator(): void
    {
        Storage::fake('portrait');
        Profile::factory()->create(['key' => 'main', 'is_published' => true]);

        $this->post('/dashboard/profile/portrait', ['portrait' => $this->png()])->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)
            ->withSession(['auth_version' => $user->auth_version])
            ->post('/dashboard/profile/portrait', ['portrait' => $this->png()])
            ->assertForbidden();

        self::assertNull(Profile::query()->firstOrFail()->portrait_path);
    }

    public function test_an_administrator_uploads_replaces_and_removes_the_portrait(): void
    {
        Storage::fake('portrait');
        Profile::factory()->create(['key' => 'main', 'is_published' => true]);
        $administrator = User::factory()->administrator()->create();
        $session = ['auth_version' => $administrator->auth_version];

        self::assertNull(app(PublishedPortrait::class)->url());

        $this->actingAs($administrator)->withSession($session)
            ->post('/dashboard/profile/portrait', ['portrait' => $this->png()])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $profile = Profile::query()->firstOrFail();
        $first = $profile->portrait_path;
        self::assertNotNull($first);
        Storage::disk('portrait')->assertExists($first);
        self::assertSame(1, AuditEvent::query()->where('action', 'profile.portrait_updated')->count());

        $url = app(PublishedPortrait::class)->url();
        self::assertNotNull($url);
        $response = $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        self::assertStringStartsWith('image/', (string) $response->headers->get('Content-Type'));
        $this->get('/media/portrait/0000000000000000')->assertNotFound();

        $this->actingAs($administrator)->withSession($session)
            ->post('/dashboard/profile/portrait', ['portrait' => $this->png()])
            ->assertSessionHasNoErrors();
        $second = Profile::query()->firstOrFail()->portrait_path;
        self::assertNotSame($first, $second);
        Storage::disk('portrait')->assertMissing($first);

        $this->actingAs($administrator)->withSession($session)
            ->delete('/dashboard/profile/portrait')
            ->assertRedirect();
        self::assertNull(Profile::query()->firstOrFail()->portrait_path);
        Storage::disk('portrait')->assertMissing((string) $second);
        self::assertNull(app(PublishedPortrait::class)->url());
    }

    public function test_the_portrait_stays_private_while_the_profile_is_unpublished_and_rejects_non_images(): void
    {
        Storage::fake('portrait');
        Profile::factory()->create(['key' => 'main', 'is_published' => false]);
        $administrator = User::factory()->administrator()->create();
        $session = ['auth_version' => $administrator->auth_version];

        $this->actingAs($administrator)->withSession($session)
            ->post('/dashboard/profile/portrait', ['portrait' => UploadedFile::fake()->create('note.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('portrait');

        $this->actingAs($administrator)->withSession($session)
            ->post('/dashboard/profile/portrait', ['portrait' => $this->png()])
            ->assertSessionHasNoErrors();

        self::assertNull(app(PublishedPortrait::class)->url());
        $checksum = (string) Profile::query()->firstOrFail()->portrait_checksum;
        auth()->logout();
        $this->get('/media/portrait/'.substr($checksum, 0, 16))->assertNotFound();
    }

    private function png(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('photo.png', (string) base64_decode(self::PNG, true));
    }
}
