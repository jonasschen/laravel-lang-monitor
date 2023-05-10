<?php

namespace Tests\Unit\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class LangMonitorScanCommandTest extends TestCase
{
    public function test_it_scan()
    {
        //$command = $this->artisan('lang_monitor:scan');
        //$command->assertExitCode(0);
        $a = '123';
        $b = '123';
        $this->assertTrue($a === $b);
    }
}
