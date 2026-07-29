<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_redirects_to_the_default_locale(): void
    {
        $this->withoutVite();

        $this->get('/')
            ->assertStatus(301)
            ->assertRedirect('/fr');
    }
}
