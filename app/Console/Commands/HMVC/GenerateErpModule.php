<?php

namespace App\Console\Commands\HMVC;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateErpModule extends Command
{

    protected $signature = 'make:erp-module
                            {module : Module name (e.g., HR or Recruitment/AgencyContract)}
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

    protected $description = 'Generate HMVC ERP module skeleton and optional parts';

    protected string $moduleInput;
    protected string $moduleStudlyPath;
    protected string $moduleName;          // last segment only (Studly)
    protected string $modulePath;          // full disk path

    protected string $moduleViewNamespace; // snake of last segment
    protected string $moduleRouteKey;
    public function handle(): int
    {
        // Read raw input (can include slashes for nested modules)
        $this->moduleInput = trim($this->argument('module'));

        // Normalize separators to forward slash
        $relative = str_replace(['\\'], '/', $this->moduleInput);

        // Convert each segment to StudlyCase for folder + namespace consistency
        $this->moduleStudlyPath = collect(explode('/', $relative))
            ->filter()
            ->map(fn ($p) => Str::studly($p))
            ->implode('/');

        // Last segment only (HR, AgencyContract, Reports, etc.)
        $this->moduleName = Str::afterLast($this->moduleStudlyPath, '/');

        // Physical module folder
        $this->modulePath = base_path('Erp/' . $this->moduleStudlyPath);

        // For stubs (views + routes)
        $this->moduleViewNamespace = Str::snake($this->moduleName);          // AgencyContract -> agency_contract
        $this->moduleRouteKey      = Str::kebab($relative);                  // Recruitment/AgencyContract -> recruitment/agency-contract

        // Always ensure module root exists
        if (!File::exists($this->modulePath)) {
            File::makeDirectory($this->modulePath, 0755, true);
            $this->info("Module created at: {$this->modulePath}");
        }

        // If user did not pass any flags => create only the basic module folders you want
        if (!$this->anyOptionSelected()) {
            $this->generateModuleSkeleton();
            $this->info("No flags used. Only the base module folders were created.");
            return self::SUCCESS;
        }

        // --all => skeleton + everything
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

        // Always create skeleton first when generating any file
        $this->generateModuleSkeleton();

        if ($this->option('provider'))   $this->generateServiceProvider();
        if ($this->option('routes'))     $this->generateRoutes();
        if ($this->option('views'))      $this->generateViews();

        if ($this->option('model'))      $this->generateModel();
        if ($this->option('controller')) $this->generateController();
        if ($this->option('api'))        $this->generateApiController();
        if ($this->option('migration'))  $this->generateMigration();
        if ($this->option('datatable'))  $this->generateDatatable();
        if ($this->option('service'))    $this->generateService();
        if ($this->option('repository')) $this->generateRepository();
        if ($this->option('resource'))   $this->generateResource();

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
     * Creates the base folders you asked for:
     * module root contains: App, resources, routes, database
     * and inside App: Models, Providers, Services, Repositories, Http (Controllers + Requests)
     */
    protected function generateModuleSkeleton(): void
    {
        // Top-level module folders
        $this->createDirectory($this->modulePath . '/App');
        $this->createDirectory($this->modulePath . '/resources');
        $this->createDirectory($this->modulePath . '/routes');
        $this->createDirectory($this->modulePath . '/database');

        // App structure
        $this->createDirectory($this->modulePath . '/App/Models');
        $this->createDirectory($this->modulePath . '/App/Providers');
        $this->createDirectory($this->modulePath . '/App/Services');
        $this->createDirectory($this->modulePath . '/App/Repositories');

        $this->createDirectory($this->modulePath . '/App/Http/Controllers');
        $this->createDirectory($this->modulePath . '/App/Http/Requests');

        // Optional common folders
        $this->createDirectory($this->modulePath . '/App/DataTables');
        $this->createDirectory($this->modulePath . '/App/Http/Resources');

        // resources + database common folders
        $this->createDirectory($this->modulePath . '/resources/views');
        $this->createDirectory($this->modulePath . '/database/migrations');
    }

    protected function getStub(string $type): string
    {
        return file_get_contents(resource_path("stubs/erp-module/{$type}.stub"));
    }

    protected function applyModuleReplacements(string $template): string
    {
        return str_replace(
            ['{{moduleStudlyPath}}', '{{moduleName}}', '{{moduleViewNamespace}}', '{{moduleRouteKey}}'],
            [$this->moduleStudlyPath, $this->moduleName, $this->moduleViewNamespace, $this->moduleRouteKey],
            $template
        );
    }

    protected function generateController(): void
    {
        $path = $this->modulePath . '/App/Http/Controllers';
        $template = $this->applyModuleReplacements($this->getStub('Controller'));
        file_put_contents($path . '/ModuleController.php', $template);
        $this->info("Controller created.");
    }

    protected function generateApiController(): void
    {
        $path = $this->modulePath . '/App/Http/Controllers';
        $template = $this->applyModuleReplacements($this->getStub('ApiController'));
        file_put_contents($path . '/ApiModuleController.php', $template);
        $this->info("API Controller created.");
    }

    protected function generateModel(): void
    {
        $path = $this->modulePath . '/App/Models';
        $template = $this->applyModuleReplacements($this->getStub('Model'));
        file_put_contents($path . '/ModuleModel.php', $template);
        $this->info("Model created.");
    }

    protected function generateMigration(): void
    {
        $path = $this->modulePath . '/database/migrations';

        $tableName = Str::plural(Str::snake($this->moduleName));
        $migrationName = date('Y_m_d_His') . "_create_{$tableName}_table.php";

        $template = $this->applyModuleReplacements($this->getStub('Migration'));
        $template = str_replace(['{{tableName}}'], [$tableName], $template);

        file_put_contents($path . '/' . $migrationName, $template);
        $this->info("Migration created: {$migrationName}");
    }

    protected function generateDatatable(): void
    {
        $path = $this->modulePath . '/App/DataTables';
        $template = $this->applyModuleReplacements($this->getStub('Datatable'));
        file_put_contents($path . '/ModuleDataTables.php', $template);
        $this->info("Datatable created.");
    }

    protected function generateService(): void
    {
        $path = $this->modulePath . '/App/Services';
        $template = $this->applyModuleReplacements($this->getStub('Service'));
        file_put_contents($path . '/ModuleService.php', $template);
        $this->info("Service created.");
    }

    protected function generateRepository(): void
    {
        $path = $this->modulePath . '/App/Repositories';
        $template = $this->applyModuleReplacements($this->getStub('Repository'));
        file_put_contents($path . '/ModuleRepository.php', $template);
        $this->info("Repository created.");
    }

    protected function generateServiceProvider(): void
    {
        $path = $this->modulePath . '/App/Providers';
        $template = $this->applyModuleReplacements($this->getStub('ModuleServiceProvider'));
        file_put_contents($path . '/ModuleServiceProvider.php', $template);
        $this->info("ModuleServiceProvider created.");
    }

    protected function generateResource(): void
    {
        $path = $this->modulePath . '/App/Http/Resources';
        $template = $this->applyModuleReplacements($this->getStub('Resource'));
        file_put_contents($path . '/ModuleResource.php', $template);
        $this->info("Resource created.");
    }

    protected function generateViews(): void
    {
        $path = $this->modulePath . '/resources/views';
        $this->createDirectory($path);
        $this->info("Views folder ready.");
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
