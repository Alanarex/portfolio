<?php

declare(strict_types=1);

namespace Modules\Contact\Providers;

use Illuminate\Support\ServiceProvider;

final class ContactServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'contact');
    }
}
