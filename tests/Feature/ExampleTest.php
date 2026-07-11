<?php

declare(strict_types=1);

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_liveness_endpoint_returns_a_successful_response_with_request_id(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
        $response->assertHeader('X-Request-ID');
    }

    public function test_valid_request_id_is_propagated(): void
    {
        $this->withHeader('X-Request-ID', 'test-request_123')
            ->get('/up')
            ->assertHeader('X-Request-ID', 'test-request_123');
    }

    public function test_invalid_request_id_is_replaced(): void
    {
        $response = $this->withHeader('X-Request-ID', "invalid\nvalue")->get('/up');
        $this->assertNotSame("invalid\nvalue", $response->headers->get('X-Request-ID'));
    }
}
