<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class LegalNoticeTest extends TestCase
{
    public function test_legal_notice_is_public_and_lists_host_and_credits(): void
    {
        config(['legal.publisher_name' => 'Éditeur Test', 'legal.publisher_email' => 'legal@example.test']);

        $this->get('/mentions-legales')
            ->assertOk()
            ->assertSee('Mentions légales')
            ->assertSee('Éditeur Test')
            ->assertSee('legal@example.test')
            ->assertSee('RES1N')
            ->assertSee('Hébergement');
    }

    public function test_legal_notice_omits_unset_publisher_details(): void
    {
        config(['legal.publisher_name' => null, 'legal.publisher_email' => null, 'legal.updated_at' => null]);

        $this->get('/mentions-legales')
            ->assertOk()
            ->assertDontSee('mailto:', false)
            ->assertDontSee('Dernière mise à jour');
    }

    public function test_footer_links_to_legal_notice(): void
    {
        $this->get('/')->assertSee(route('legal'), false);
    }
}
