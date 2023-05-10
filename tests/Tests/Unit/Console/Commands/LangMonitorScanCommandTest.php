<?php

namespace Tests\Unit\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LangMonitorScanCommandTest extends TestCase
{
    public function test_it_scan()
    {
        $command = $this->artisan('lang_monitor:scan');
        $command->assertExitCode(0);
    }
}
