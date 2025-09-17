<?php

namespace Jonasschen\LaravelLangMonitor\Enums;

use ReflectionClass;

enum ResponseLogEnum: string
{
    case ALERT = 'alert';
    case COMMENT = 'comment';
    case ERROR = 'error';
    case INFO = 'info';
    case LINE = 'line';

    public static function getConstants(): array
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
