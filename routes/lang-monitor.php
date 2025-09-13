<?php
use Illuminate\Support\Facades\Route;
use Jonasschen\LaravelLangMonitor\Http\Controllers\TranslationMonitorController;


Route::middleware(config('lang-monitor.middleware', ['web', 'auth']))
    ->prefix(config('lang-monitor.prefix', 'lang-monitor'))
    ->group(function () {
        Route::get('/', [TranslationMonitorController::class, 'index'])
            ->name('lang-monitor.index');
        Route::post('/save', [TranslationMonitorController::class, 'save'])
            ->name('lang-monitor.save');
    });
