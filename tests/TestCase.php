<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('inertia.pages.paths', [resource_path('js/Pages')]);
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->withoutVite();
    }
}
