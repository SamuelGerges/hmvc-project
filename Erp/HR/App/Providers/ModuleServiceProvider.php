<?php
namespace Erp\HR\App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Module root folder:
        // .../Erp/<Module>/App/Providers  -> go up 2 levels -> .../Erp/<Module>
        $moduleRoot = dirname(__DIR__, 2);

        // Web + API routes
        if (file_exists($moduleRoot . '/routes/web.php')) {
            $this->loadRoutesFrom($moduleRoot . '/routes/web.php');
        }

        if (file_exists($moduleRoot . '/routes/api.php')) {
            $this->loadRoutesFrom($moduleRoot . '/routes/api.php');
        }

        // Views namespace: h_r::index
        if (is_dir($moduleRoot . '/resources/views')) {
            $this->loadViewsFrom($moduleRoot . '/resources/views', 'h_r');
        }

        // Migrations
        if (is_dir($moduleRoot . '/database/migrations')) {
            $this->loadMigrationsFrom($moduleRoot . '/database/migrations');
        }
    }
}
