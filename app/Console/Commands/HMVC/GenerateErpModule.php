<?php

namespace App\Console\Commands\HMVC;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateErpModule extends Command
{
    protected $signature = 'make:erp-module
                            {module : The name of the ERP module (e.g., HR)}
                            {model? : Optional (ignored). Kept only to not break old usage.}
                            {--all : Generate all files and folders}
                            {--controller : Generate controller}
                            {--api : Generate API controller}
                            {--model : Generate model}
                            {--migration : Generate migration}
                            {--datatable : Generate datatable}
                            {--service : Generate service}
                            {--repository : Generate repository}
                            {--provider : Generate service provider}
                            {--resource : Generate resource}
                            {--views : Generate views folder}
                            {--routes : Generate routes files}';

    protected $description = 'Generate HMVC structure for ERP modules (module-only)';

    protected string $moduleName;
    protected string $modulePath;

    // For stubs that need route/view keys
    protected string $moduleViewNamespace;
    protected string $moduleRouteKey;
    protected string $moduleTableId;

    public function handle(): int
    {
        $this->moduleName = Str::studly($this->argument('module'));
        $this->modulePath = base_path('Erp/' . $this->moduleName);

        $this->moduleViewNamespace = Str::snake($this->moduleName); // hr
        $this->moduleRouteKey      = Str::kebab($this->moduleName); // hr
        $this->moduleTableId       = Str::kebab($this->moduleName); // hr

        // Create module directory if it doesn't exist
        if (!File::exists($this->modulePath)) {
            File::makeDirectory($this->modulePath, 0755, true);
            $this->info("Module {$this->moduleName} created at {$this->modulePath}");
        }

        // --all means: module skeleton + all module-level files
        if ($this->option('all')) {
            $this->generateModuleSkeleton();
            $this->generateServiceProvider();
            $this->generateRoutes();
            $this->generateViews();

            $this->generateModel();
            $this->generateController();
            $this->generateApiController();
            $this->generateMigration();
            $this->generateDatatable();
            $this->generateService();
            $this->generateRepository();
            $this->generateResource();

            return self::SUCCESS;
        }

        // Module skeleton pieces
        if ($this->option('views')) $this->generateViews();
        if ($this->option('routes')) $this->generateRoutes();
        if ($this->option('provider')) $this->generateServiceProvider();

        // Module-level generation (even if user passes --model, --controller, etc.)
        if ($this->option('model')) $this->generateModel();
        if ($this->option('controller')) $this->generateController();
        if ($this->option('api')) $this->generateApiController();
        if ($this->option('migration')) $this->generateMigration();
        if ($this->option('datatable')) $this->generateDatatable();
        if ($this->option('service')) $this->generateService();
        if ($this->option('repository')) $this->generateRepository();
        if ($this->option('resource')) $this->generateResource();

        // If user runs command with no flags, create skeleton only
        if (!$this->anyOptionSelected()) {
            $this->generateModuleSkeleton();
            $this->info("No flags used, so only module folders were created.");
        }

        return self::SUCCESS;
    }

    protected function anyOptionSelected(): bool
    {
        return $this->option('all')
            || $this->option('controller')
            || $this->option('api')
            || $this->option('model')
            || $this->option('migration')
            || $this->option('datatable')
            || $this->option('service')
            || $this->option('repository')
            || $this->option('provider')
            || $this->option('resource')
            || $this->option('views')
            || $this->option('routes');
    }

    /**
     * Creates base folders inside every module
     */
    protected function generateModuleSkeleton(): void
    {
        // App tree
        $this->createDirectory($this->modulePath . '/App/Datatables');
        $this->createDirectory($this->modulePath . '/App/Http/Controllers/Admin');
        $this->createDirectory($this->modulePath . '/App/Http/Controllers/Api');
        $this->createDirectory($this->modulePath . '/App/Http/Resources');
        $this->createDirectory($this->modulePath . '/App/Models');
        $this->createDirectory($this->modulePath . '/App/Providers');
        $this->createDirectory($this->modulePath . '/App/Repositories');
        $this->createDirectory($this->modulePath . '/App/Services');

        // Top-level module folders
        $this->createDirectory($this->modulePath . '/routes');
        $this->createDirectory($this->modulePath . '/database/migrations');
        $this->createDirectory($this->modulePath . '/resources/views');

        $this->info("Module skeleton created.");
    }

    protected function getStub(string $type): string
    {
        return file_get_contents(resource_path("stubs/erp-module/{$type}.stub"));
    }

    /**
     * Replaces module placeholders used by your new stubs
     */
    protected function applyModuleReplacements(string $template): string
    {
        return str_replace(
            ['{{moduleName}}', '{{moduleViewNamespace}}', '{{moduleRouteKey}}', '{{moduleTableId}}'],
            [$this->moduleName, $this->moduleViewNamespace, $this->moduleRouteKey, $this->moduleTableId],
            $template
        );
    }

    protected function generateController(): void
    {
        $path = $this->modulePath . '/App/Http/Controllers/Admin';
        $this->createDirectory($path);

        $template = $this->applyModuleReplacements($this->getStub('AdminController'));
        file_put_contents($path . '/ModuleController.php', $template);

        $this->info("Admin ModuleController created.");
    }

    protected function generateApiController(): void
    {
        $path = $this->modulePath . '/App/Http/Controllers/Api';
        $this->createDirectory($path);

        $template = $this->applyModuleReplacements($this->getStub('ApiController'));
        file_put_contents($path . '/ModuleController.php', $template);

        $this->info("API ModuleController created.");
    }

    protected function generateModel(): void
    {
        $path = $this->modulePath . '/App/Models';
        $this->createDirectory($path);

        $template = $this->applyModuleReplacements($this->getStub('Model'));
        file_put_contents($path . '/ModuleModel.php', $template);

        $this->info("ModuleModel created.");
    }

    protected function generateMigration(): void
    {
        $path = $this->modulePath . '/database/migrations';
        $this->createDirectory($path);

        // Table name from module name (example: HR => hrs) adjust if you want
        $tableName = Str::plural(Str::snake($this->moduleName));
        $migrationName = date('Y_m_d_His') . "_create_{$tableName}_table.php";

        $template = str_replace(['{{tableName}}'], [$tableName], $this->getStub('Migration'));
        file_put_contents($path . '/' . $migrationName, $template);

        $this->info("Migration created: {$migrationName}");
    }

    protected function generateDatatable(): void
    {
        $path = $this->modulePath . '/App/Datatables';
        $this->createDirectory($path);

        $template = $this->applyModuleReplacements($this->getStub('Datatable'));
        file_put_contents($path . '/ModuleDataTables.php', $template);

        $this->info("ModuleDataTables created.");
    }

    protected function generateService(): void
    {
        $path = $this->modulePath . '/App/Services';
        $this->createDirectory($path);

        $template = $this->applyModuleReplacements($this->getStub('Service'));
        file_put_contents($path . '/ModuleService.php', $template);

        $this->info("ModuleService created.");
    }

    protected function generateRepository(): void
    {
        $path = $this->modulePath . '/App/Repositories';
        $this->createDirectory($path);

        $template = $this->applyModuleReplacements($this->getStub('Repository'));
        file_put_contents($path . '/ModuleRepository.php', $template);

        $this->info("ModuleRepository created.");
    }

    protected function generateServiceProvider(): void
    {
        $path = $this->modulePath . '/App/Providers';
        $this->createDirectory($path);

        $template = $this->applyModuleReplacements($this->getStub('ModuleServiceProvider'));
        file_put_contents($path . '/ModuleServiceProvider.php', $template);

        $this->info("ModuleServiceProvider created.");
    }

    protected function generateResource(): void
    {
        $path = $this->modulePath . '/App/Http/Resources';
        $this->createDirectory($path);

        $template = $this->applyModuleReplacements($this->getStub('Resource'));
        file_put_contents($path . '/ModuleResource.php', $template);

        $this->info("ModuleResource created.");
    }

    protected function generateViews(): void
    {
        $path = $this->modulePath . '/resources/views';
        $this->createDirectory($path);

        $this->info("Views directory created.");
    }

    protected function generateRoutes(): void
    {
        $path = $this->modulePath . '/routes';
        $this->createDirectory($path);

        $web = $this->applyModuleReplacements($this->getStub('WebRoutes'));
        file_put_contents($path . '/web.php', $web);

        $api = $this->applyModuleReplacements($this->getStub('ApiRoutes'));
        file_put_contents($path . '/api.php', $api);

        $this->info("Routes created.");
    }

    protected function createDirectory(string $path): void
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}
