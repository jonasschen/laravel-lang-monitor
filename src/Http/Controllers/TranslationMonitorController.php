<?php
namespace Jonasschen\LaravelLangMonitor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Jonasschen\LaravelLangMonitor\Services\LangMonitorService;

final class TranslationMonitorController extends Controller
{
    public function __construct(
        private LangMonitorService $langMonitorService,
    ) {

    }

    public function index()
    {
        return view('lang-monitor::monitor', [
            'locales' => config('lang-monitor.locales', ['en', 'pt-BR']),
        ]);
    }

    /**
     * Recebe o payload JSON editado e persiste nas lang files (opcional),
     * ou salva um arquivo consolidado em storage.
     */
    public function save(Request $request)
    {
        $payload = $request->validate([
            'data'     => 'required|array',              // objeto { key: value }
            'format'   => 'required|in:json,php',
        ]);

        $map    = $payload['data'];
        $format = $payload['format']; // 'json' ou 'php'

        // caminho base (ex.: storage/app/lang-monitor)
        $baseDir = storage_path('app/lang-monitor');
        if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
            return response()->json(['message' => 'Falha ao criar diretório de destino.'], 500);
        }

        $filename = 'updated_' . date('Ymd_His') . ($format === 'php' ? '.php' : '.json');
        $path = $baseDir . DIRECTORY_SEPARATOR . $filename;

        // conteúdo conforme formato
        if ($format === 'php') {
            $content = "<?php\n\nreturn [\n";
            ksort($map);
            foreach ($map as $k => $v) {
                $k = str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$k);
                $v = str_replace(["\\", "'"], ["\\\\", "\\'"], (string)($v ?? ''));
                $content .= "    '{$k}' => '{$v}',\n";
            }
            $content .= "];\n";
        } else {
            // json
            $content = json_encode($map, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }

        if (file_put_contents($path, $content) === false) {
            return response()->json(['message' => 'Falha ao escrever o arquivo.'], 500);
        }

        return response()->json(['ok' => true, 'path' => $path]);
    }

    public function getConfig(): JsonResponse
    {
        // Retorna a config atual como JSON
        $cfg = config('lang-monitor');

        return response()->json([
            'ok' => true,
            'config' => $cfg,
        ]);
    }

    public function saveConfig(Request $request): JsonResponse
    {
        // Validação básica
        $data = $request->validate([
            'abort_if_directory_doesnt_exists' => 'required|boolean',
            'abort_if_lang_file_doesnt_exists' => 'required|boolean',
            'scan_for_unused_translations'     => 'required|boolean',
            'directories_to_search'            => 'required|array',
            'directories_to_search.*'          => 'string',
            'extensions_to_search'             => 'required|array',
            'extensions_to_search.*'           => 'string',
            'lang_files'                       => 'required|array',
            'lang_files.*'                     => 'string',
            'locale'                           => 'required|string',
            'middleware'                       => 'required|array',
            'middleware.*'                     => 'string',
            'ui_prefix'                        => 'required|string',
        ]);

        // Gera PHP de config (sem comentários) e escreve em config/lang-monitor.php
        $configPath = config_path('lang-monitor.php');
        $php = $this->exportConfigPhp($data);

        if (@file_put_contents($configPath, $php) === false) {
            return response()->json(['ok' => false, 'message' => 'Falha ao escrever config/lang-monitor.php'], 500);
        }

        return response()->json(['ok' => true, 'path' => $configPath]);
    }

    /**
     * Gera um arquivo PHP de config com return [ ... ];
     */
    protected function exportConfigPhp(array $cfg): string
    {
        // Mantém a ordem das chaves principais
        $ordered = [
            'abort_if_directory_doesnt_exists' => (bool)($cfg['abort_if_directory_doesnt_exists'] ?? false),
            'abort_if_lang_file_doesnt_exists' => (bool)($cfg['abort_if_lang_file_doesnt_exists'] ?? false),
            'scan_for_unused_translations'     => (bool)($cfg['scan_for_unused_translations'] ?? true),
            'directories_to_search'            => array_values($cfg['directories_to_search'] ?? []),
            'extensions_to_search'             => array_values($cfg['extensions_to_search'] ?? []),
            'lang_files'                       => array_values($cfg['lang_files'] ?? []),
            'locale'                           => (string)($cfg['locale'] ?? 'en.utf8'),
            'middleware'                       => array_values($cfg['middleware'] ?? ['web']),
            'ui_prefix'                        => (string)($cfg['ui_prefix'] ?? 'lang-monitor'),
        ];

        // Serializer simples e legível
        $exportScalar = function ($v) {
            if (is_bool($v)) return $v ? 'true' : 'false';
            // aspas simples com escapes
            $s = str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$v);
            return "'{$s}'";
        };
        $exportArray = function ($arr) use ($exportScalar) {
            $lines = [];
            foreach ($arr as $item) {
                $lines[] = '        '.$exportScalar($item).',';
            }
            return "[\n".implode("\n", $lines)."\n    ]";
        };

        $lines = [];
        $lines[] = "<?php\n";
        $lines[] = "return [";
        $lines[] = "    'abort_if_directory_doesnt_exists' => ".($ordered['abort_if_directory_doesnt_exists'] ? 'true' : 'false').",";
        $lines[] = "    'abort_if_lang_file_doesnt_exists' => ".($ordered['abort_if_lang_file_doesnt_exists'] ? 'true' : 'false').",";
        $lines[] = "    'scan_for_unused_translations' => ".($ordered['scan_for_unused_translations'] ? 'true' : 'false').",";

        $lines[] = "    'directories_to_search' => ".$exportArray($ordered['directories_to_search']).",";
        $lines[] = "    'extensions_to_search'  => ".$exportArray($ordered['extensions_to_search']).",";
        $lines[] = "    'lang_files'            => ".$exportArray($ordered['lang_files']).",";

        $lines[] = "    'locale'     => ".$exportScalar($ordered['locale']).",";
        $lines[] = "    'middleware' => ".$exportArray($ordered['middleware']).",";
        $lines[] = "    'ui_prefix'  => ".$exportScalar($ordered['ui_prefix']).",";
        $lines[] = "];\n";

        return implode("\n", $lines);
    }

    public function scan(): JsonResponse
    {
        $response = $this->langMonitorService->run(true);

        return response()->json($response);
    }
}
