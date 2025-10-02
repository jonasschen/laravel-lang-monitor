<?php

namespace Jonasschen\LaravelLangMonitor\Console\Commands;

use Illuminate\Console\Command;
use Jonasschen\LaravelLangMonitor\Services\LangMonitorService;

final class LangMonitorScanCommand extends Command
{
    protected $signature = 'lang_monitor:scan {--export_json_file= : [DEPRECATED] Equal to export_missed_json_file. Kept for compatibility} {--export_php_file= : [DEPRECATED] Equal to export_missed_php_file. Kept for compatibility} {--export_missed_json_file= : The filename where you wish to export the JSON file with missed translations} {--export_missed_php_file= : The filename where you wish to export the PHP file with missed translations} {--export_missed_txt_file= : The filename where you wish to export the text file with missed translations} {--export_unused_json_file= : The filename where you wish to export the JSON file with unused keys} {--export_unused_php_file= : The filename where you wish to export the PHP file with unused keys} {--export_unused_txt_file= : The filename where you wish to export the text file with unused keys}';

    protected $description = 'Searches for all @lang(), __() and trans() keys in all configured files and check if them exists in the configured lang files.';

    public function __construct(
        private readonly LangMonitorService $langMonitorService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $response = $this->langMonitorService->run(false, $this->options());

        if (isset($response['log'])) {
            foreach ($response['log'] as $log) {
                $this->{$log['type']}($log['message']);
            }
        }

        return $response['success'] ? '0' : '1';
    }
}
