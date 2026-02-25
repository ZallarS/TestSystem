<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Providers\PluginServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Facades\Hook;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(PluginServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Blade::directive('hook', function ($expression) {
            return "<?php Hook::doAction($expression); ?>";
        });
    }
}
