<?php

namespace Jonasschen\LaravelLangMonitor\Facades;

use Illuminate\Support\Facades\Facade;

class LangMonitorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-lang-monitor';
    }
}
