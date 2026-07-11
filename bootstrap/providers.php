<?php

declare(strict_types=1);

use App\Core\Modules\ModuleDiscovery;
use App\Providers\AppServiceProvider;

return array_merge([
    AppServiceProvider::class,
], ModuleDiscovery::providers(dirname(__DIR__)));
