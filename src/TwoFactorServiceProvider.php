<?php

namespace mannaf\Laravel2FA;

use Illuminate\Support\ServiceProvider;
use Mannaf\Laravel2FA\Middleware\TwoFactorMiddleware;

class TwoFactorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/config/twofactor.php' => config_path('twofactor.php'),
        ], 'twofactor-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/views' => resource_path('views/vendor/twofactor'),
        ], 'twofactor-views');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/views', 'twofactor');

        // Register middleware
        $this->app['router']->aliasMiddleware('2fa', TwoFactorMiddleware::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/twofactor.php', 'twofactor');
    }
}
