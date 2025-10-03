<?php

namespace Jonasschen\LaravelLangMonitor\Providers;

use Illuminate\Support\ServiceProvider;
use Jonasschen\LaravelLangMonitor\Console\Commands\LangMonitorScanCommand;
use Jonasschen\LaravelLangMonitor\LaravelLangMonitor;

class LangMonitorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $baseDir = __DIR__ . '/../../';
        $this->loadRoutesFrom($baseDir . 'routes/lang-monitor.php');
        $this->loadViewsFrom($baseDir . 'resources/views', 'lang-monitor');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $baseDir . 'config/config.php' => config_path('lang-monitor.php'),
            ], 'lang-monitor-config');

            $this->publishes([
                $baseDir . 'public' => public_path('vendor/lang-monitor'),
            ], 'lang-monitor-assets');

            $this->publishes([
                $baseDir . 'resources/views' => resource_path('views/vendor/lang-monitor'),
            ], 'lang-monitor-views');

            // Registering package commands.
            $this->commands([
                LangMonitorScanCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $baseDir = __DIR__ . '/../../';

        // Automatically apply the package configuration
        $this->mergeConfigFrom($baseDir . 'config/config.php', 'lang-monitor');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-lang-monitor', function () {
            return new LaravelLangMonitor();
        });
    }
}
