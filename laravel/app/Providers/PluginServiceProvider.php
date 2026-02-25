<?php

namespace App\Providers;

use App\Services\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager();
        });

        $this->app->singleton('hook.manager', function ($app) {
            return new \App\Services\HookManager();
        });

        $this->app->booted(function () {
            app(PluginManager::class)->registerActiveProviders();
        });
    }

    public function boot()
    {
        //
    }
}
