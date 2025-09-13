<?php

namespace Jonasschen\LaravelLangMonitor;

use Illuminate\Support\ServiceProvider;
use Jonasschen\LaravelLangMonitor\Console\Commands\LangMonitorScanCommand;

class LaravelLangMonitorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/lang-monitor.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lang-monitor');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('lang-monitor.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/lang-monitor'),
            ], 'lang-monitor-assets');


            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/lang-monitor'),
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
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'lang-monitor');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-lang-monitor', function () {
            return new LaravelLangMonitor();
        });
    }
}
