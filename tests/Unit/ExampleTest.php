<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Modules\ModuleDiscovery;
use Modules\Foundation\Providers\FoundationServiceProvider;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_module_provider_is_discovered(): void
    {
        $this->assertSame([FoundationServiceProvider::class], ModuleDiscovery::providers(dirname(__DIR__, 2)));
    }
}
