<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LangMonitorScanCommandTest extends TestCase
{
    public function testItScan()
    {
        $command = $this->artisan('lang_monitor:scan');
        $command->assertExitCode(0);
    }
}
