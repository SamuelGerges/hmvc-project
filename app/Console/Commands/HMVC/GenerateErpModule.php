<?php

namespace App\Console\Commands\HMVC;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateErpModule extends Command
{
    protected $signature = 'make:erp-module
                            {module : The name of the ERP module (e.g., BusinessDevelopment)}
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

    protected $description = 'Generate HMVC structure for ERP modules';

    protected $moduleName;
    protected $modulePath;

    public function handle()
    {
        $this->moduleName = $this->argument('module');
        $this->modulePath = 'Erp/' . $this->moduleName;

        // Create module directory if it doesn't exist
        if (!File::exists($this->modulePath)) {
            File::makeDirectory($this->modulePath, 0755, true);
            $this->info("Module {$this->moduleName} created successfully.");
        }

        if ($this->option('all')) {
            $this->generateAll();
            return;
        }

        if ($this->option('controller')) $this->generateController();
        if ($this->option('api')) $this->generateApiController();
        if ($this->option('model')) $this->generateModel();
        if ($this->option('migration')) $this->generateMigration();
        if ($this->option('datatable')) $this->generateDatatable();
        if ($this->option('service')) $this->generateService();
        if ($this->option('repository')) $this->generateRepository();
        if ($this->option('provider')) $this->generateServiceProvider();
        if ($this->option('resource')) $this->generateResource();
        if ($this->option('views')) $this->generateViews();
        if ($this->option('routes')) $this->generateRoutes();
    }

    protected function generateAll()
    {
        $this->generateModel();
        $this->generateController();
        $this->generateApiController();
        $this->generateMigration();
        $this->generateDatatable();
        $this->generateServiceProvider();
        $this->generateViews();
        $this->generateRoutes();
    }

    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/erp-module/$type.stub"));
    }

    protected function generateController()
    {
        $controllerPath = $this->modulePath . '/App/Http/Controllers/Admin';
        $this->createDirectory($controllerPath);

        $controllerTemplate = str_replace(
            ['{{moduleName}}', '{{moduleViewNamespace}}', '{{moduleRouteKey}}'],
            [$this->moduleName, Str::snake($this->moduleName), Str::kebab($this->moduleName)],
            $this->getStub('Controller')
        );

        file_put_contents("{$controllerPath}/{$this->moduleName}Controller.php", $controllerTemplate);
        $this->info("Controller created successfully.");
    }

    protected function generateApiController()
    {
        $controllerPath = $this->modulePath . '/App/Http/Controllers/Api';
        $this->createDirectory($controllerPath);

        $controllerTemplate = str_replace(
            ['{{moduleName}}', '{{moduleViewNamespace}}', '{{moduleRouteKey}}'],
            [$this->moduleName, Str::snake($this->moduleName), Str::kebab($this->moduleName)],
            $this->getStub('ApiController')
        );

        file_put_contents("{$controllerPath}/{$this->moduleName}Controller.php", $controllerTemplate);
        $this->info("API Controller created successfully.");
    }

    protected function generateModel()
    {
        $modelPath = $this->modulePath . '/App/Models';
        $this->createDirectory($modelPath);

        $modelTemplate = str_replace(
            ['{{moduleName}}'],
            [$this->moduleName],
            $this->getStub('Model')
        );

        file_put_contents("{$modelPath}/{$this->moduleName}Model.php", $modelTemplate);
        $this->info("Model created successfully.");
    }

    protected function generateMigration()
    {
        $migrationPath = $this->modulePath . '/database/migrations';
        $this->createDirectory($migrationPath);

        // Default table name derived from module name
        $tableName = Str::plural(Str::snake($this->moduleName));
        $migrationName = date('Y_m_d_His') . "_create_{$tableName}_table.php";

        $migrationTemplate = str_replace(
            ['{{tableName}}'],
            [$tableName],
            $this->getStub('Migration')
        );

        file_put_contents("{$migrationPath}/{$migrationName}", $migrationTemplate);
        $this->info("Migration created successfully in module.");
    }

    protected function generateDatatable()
    {
        $datatablePath = $this->modulePath . '/App/DataTables';
        $this->createDirectory($datatablePath);

        $datatableTemplate = str_replace(
            ['{{moduleName}}', '{{moduleViewNamespace}}', '{{moduleTableId}}'],
            [$this->moduleName, Str::snake($this->moduleName), Str::kebab($this->moduleName)],
            $this->getStub('Datatable')
        );

        file_put_contents("{$datatablePath}/{$this->moduleName}DataTables.php", $datatableTemplate);
        $this->info("Datatable created successfully.");
    }

    protected function generateService()
    {
        $servicePath = $this->modulePath . '/App/Services';
        $this->createDirectory($servicePath);

        $serviceTemplate = str_replace(
            ['{{moduleName}}'],
            [$this->moduleName],
            $this->getStub('Service')
        );

        file_put_contents("{$servicePath}/ModuleService.php", $serviceTemplate);
        $this->info("Service created successfully.");
    }

    protected function generateRepository()
    {
        $repositoryPath = $this->modulePath . '/App/Repositories';
        $this->createDirectory($repositoryPath);

        $repositoryTemplate = str_replace(
            ['{{moduleName}}'],
            [$this->moduleName],
            $this->getStub('Repository')
        );

        file_put_contents("{$repositoryPath}/ModuleRepository.php", $repositoryTemplate);
        $this->info("Repository created successfully.");
    }

    protected function generateServiceProvider()
    {
        $providerPath = $this->modulePath . '/App/Providers';
        $this->createDirectory($providerPath);

        $providerTemplate = str_replace(
            ['{{moduleName}}', '{{moduleViewNamespace}}', '{{moduleRouteKey}}'],
            [$this->moduleName, Str::snake($this->moduleName), Str::kebab($this->moduleName)],
            $this->getStub('ServiceProvider') // keep same stub name you used
        );

        file_put_contents("{$providerPath}/{$this->moduleName}ServiceProvider.php", $providerTemplate);
        $this->info("Service Provider created successfully.");
    }

    protected function generateResource()
    {
        $resourcePath = $this->modulePath . '/App/Http/Resources';
        $this->createDirectory($resourcePath);

        $resourceTemplate = str_replace(
            ['{{moduleName}}'],
            [$this->moduleName],
            $this->getStub('Resource')
        );

        file_put_contents("{$resourcePath}/ModuleResource.php", $resourceTemplate);
        $this->info("Resource created successfully.");
    }

    protected function generateViews()
    {
        $viewsPath = $this->modulePath . '/resources/views/';
        $this->createDirectory($viewsPath);

        $this->info("Views directory created successfully.");
    }

    protected function generateRoutes()
    {
        $routesPath = $this->modulePath . '/routes';
        $this->createDirectory($routesPath);

        // Generate web.php
        $webTemplate = str_replace(
            ['{{moduleName}}', '{{moduleRouteKey}}', '{{moduleViewNamespace}}'],
            [$this->moduleName, Str::kebab($this->moduleName), Str::snake($this->moduleName)],
            $this->getStub('WebRoutes')
        );
        file_put_contents("{$routesPath}/web.php", $webTemplate);

        // Generate api.php
        $apiTemplate = str_replace(
            ['{{moduleName}}', '{{moduleRouteKey}}', '{{moduleViewNamespace}}'],
            [$this->moduleName, Str::kebab($this->moduleName), Str::snake($this->moduleName)],
            $this->getStub('ApiRoutes')
        );
        file_put_contents("{$routesPath}/api.php", $apiTemplate);

        $this->info("Routes files created successfully.");
    }

    protected function createDirectory($path)
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}
