<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ErrorPagesTest extends TestCase
{
    public function test_unknown_page_renders_branded_404(): void
    {
        $this->get('/page-qui-nexiste-pas')
            ->assertNotFound()
            ->assertSee('Page introuvable')
            ->assertSee('Retour à l’accueil')
            ->assertDontSee('class="loader"', false);
    }

    public function test_every_error_view_renders(): void
    {
        foreach ([403, 404, 419, 429, 500, 503] as $status) {
            $html = view('errors.'.$status)->render();

            $this->assertStringContainsString('Erreur '.$status, $html);
            $this->assertStringContainsString('Retour à l’accueil', $html);
        }
    }
}
