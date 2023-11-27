<?php

namespace Jonasschen\LaravelLangMonitor\Console\Commands;

use Illuminate\Console\Command;

class AITestCommand extends Command
{
    protected $signature = 'ai_test:run';

    protected $description = 'AI Test';

    public function handle()
    {
        $this->info("That's OK");

        return 0;
    }
}
