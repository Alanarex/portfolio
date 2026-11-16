<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_redirects_the_root_to_a_locale(): void
    {
        $this->withoutVite();

        $this->withHeader('Accept-Language', 'fr-FR')->get('/')
            ->assertStatus(302)
            ->assertRedirect('/fr');
    }
}
