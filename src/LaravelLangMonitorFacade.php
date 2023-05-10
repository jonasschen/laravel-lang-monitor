<?php

namespace Jonasschen\LaravelLangMonitor;

use Illuminate\Support\Facades\Facade;

class LaravelLangMonitorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-lang-monitor';
    }
}
