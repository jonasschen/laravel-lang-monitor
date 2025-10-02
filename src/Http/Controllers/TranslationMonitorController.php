<?php

namespace Jonasschen\LaravelLangMonitor\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jonasschen\LaravelLangMonitor\Services\LangMonitorService;

final class TranslationMonitorController extends Controller
{
    public function __construct(
        private LangMonitorService $langMonitorService,
    ) {}

    public function index()
    {
        return view('lang-monitor::monitor', [
            'locales' => config('lang-monitor.locales', ['en', 'pt-BR']),
        ]);
    }

    public function save(Request $request)
    {
        $payload = $request->validate([
            'data' => 'required|array',
            'format' => 'required|in:json,php,txt',
        ]);

        $map = $payload['data'];
        $format = $payload['format'];

        $baseDir = storage_path('app/lang-monitor');
        if (!is_dir($baseDir) && !mkdir($baseDir, 0o775, true) && !is_dir($baseDir)) {
            return response()->json(['message' => 'Failed to create destination directory.'], 500);
        }

        $filename = 'exported_' . date('Ymd_His') . '.' . $format;
        $path = $baseDir . DIRECTORY_SEPARATOR . $filename;

        $content = null;
        switch ($format) {
            case 'php':
                $content = "<?php\n\nreturn [\n";
                ksort($map);
                foreach ($map as $key => $value) {
                    $key = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $key);
                    $value = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) ($value ?? ''));
                    $content .= "    '{$key}' => '{$value}',\n";
                }
                $content .= "];\n";
                break;
            case 'json':
                $content = json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;
            case 'txt':
                foreach ($map as $key => $value) {
                    $key = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $key);
                    $value = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) ($value ?? ''));
                    $content .= "{$key}: {$value}\n";
                }

                break;
        }

        if (!$content || false === file_put_contents($path, $content)) {
            return response()->json(['message' => 'Failed to write file.'], 500);
        }

        return response()->json([
            'success' => true,
            'path' => $path,
        ]);
    }

    public function scan(): JsonResponse
    {
        $response = $this->langMonitorService->run(true);

        return response()->json($response);
    }
}
