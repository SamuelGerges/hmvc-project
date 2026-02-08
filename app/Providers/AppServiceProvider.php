<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\HMVC\ModuleRegistry;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $roots     = config('hmvc.roots', []);
        $providers = ModuleRegistry::discoverProviders($roots);

        foreach ($providers as $providerClass) {
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
