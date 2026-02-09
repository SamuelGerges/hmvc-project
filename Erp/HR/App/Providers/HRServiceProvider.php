<?php

namespace Erp\HR\App\Providers;

use Illuminate\Support\ServiceProvider;

class HRServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $dir = DIRECTORY_SEPARATOR;

        // Module base directory relative to project root
        // Example: Erp/HR/
        $baseDir = 'Erp' . $dir . 'HR' . $dir;

        // Load module routes
        $this->loadRoutesFrom(
            base_path($baseDir . 'routes' . $dir . 'web.php')
        );

        // Load module migrations
        $this->loadMigrationsFrom(
            base_path($baseDir . 'database' . $dir . 'migrations')
        );

        // Load module views
        // View namespace = module name (lowercase recommended)
        $this->loadViewsFrom(
            base_path($baseDir . 'resources' . $dir . 'views'),
            'h_r'
        );
    }
}
