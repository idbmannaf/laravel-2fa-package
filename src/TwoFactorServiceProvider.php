<?php

namespace Mannaf\Laravel2FA;

use Illuminate\Support\ServiceProvider;

class TwoFactorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/twofactor.php' => config_path('twofactor.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/views', 'twofactor');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/twofactor.php', 'twofactor');
    }
}
