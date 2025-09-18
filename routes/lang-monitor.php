<?php
use Illuminate\Support\Facades\Route;
use Jonasschen\LaravelLangMonitor\Http\Controllers\TranslationMonitorController;


Route::middleware(config('lang-monitor.middleware', ['web']))
    ->prefix(config('lang-monitor.ui_prefix', 'lang-monitor'))
    ->group(function () {
        Route::get('/', [TranslationMonitorController::class, 'index'])->name('lang-monitor.index');
        Route::post('/scan', [TranslationMonitorController::class, 'scan'])->name('lang-monitor.scan');
        Route::post('/save', [TranslationMonitorController::class, 'save'])->name('lang-monitor.save');
    });
