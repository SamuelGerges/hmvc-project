<?php

namespace App\Support\HMVC;

use Illuminate\Support\Str;

class ModuleRegistry
{
    public static function discoverProviders(array $roots): array
    {
        $providers = [];

        foreach ($roots as $root) {
            if (!is_dir($root)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                $path = $file->getPathname();

                if (!Str::endsWith($path, 'App/Providers/ModuleServiceProvider.php')) {
                    continue;
                }

                // Convert file path to class name
                $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                $relative = str_replace(['/', '\\'], '\\', $relative);
                $class = Str::replaceLast('.php', '', $relative);

                $providers[] = $class;
            }
        }

        return $providers;
    }
}
