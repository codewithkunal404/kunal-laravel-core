<?php

namespace KunalLaravel\Core;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use KunalLaravel\Core\Commands\MakeModuleCommand;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Auto load all module routes, views, migrations
        $this->loadModules();

        // Publish config if you want
        $this->publishes([
            __DIR__.'/../config/module.php' => config_path('module.php'),
        ], 'config');
    }

    public function register()
    {
        $this->commands([
            MakeModuleCommand::class,
        ]);
    }

    protected function loadModules()
    {
        $modulesPath = base_path('modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $module) {
            // Load routes
            if (File::exists($module.'/Routes/web.php')) {
                $this->loadRoutesFrom($module.'/Routes/web.php');
            }

            // Load migrations
            if (File::exists($module.'/Database/migrations')) {
                $this->loadMigrationsFrom($module.'/Database/migrations');
            }

            // Load views
            if (File::exists($module.'/Views')) {
                $name = basename($module);
                $this->loadViewsFrom($module.'/Views', $name);
            }
        }
    }
}
