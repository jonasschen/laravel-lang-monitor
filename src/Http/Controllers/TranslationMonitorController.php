<?php
namespace Jonasschen\LaravelLangMonitor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jonasschen\LaravelLangMonitor\Support\LangWriter;


class TranslationMonitorController extends Controller
{
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
            'data' => 'required|array', // objeto { key: value }
            'save.filename' => 'required|string',
        ]);

        $map      = $payload['data'];
        $filename = (string)$payload['save']['filename'];

        // filename seguro: letras, números, . _ - e obrigatoriamente termina com .json
        if (!preg_match('/^[A-Za-z0-9._-]+\.json$/', $filename)) {
            return response()->json(['message' => 'Nome de arquivo inválido. Use apenas letras, números, "._-" e termine com .json'], 422);
        }

        // diretório base
        $baseDir = storage_path('app/lang-monitor');

        if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
            return response()->json(['message' => 'Falha ao criar diretório de destino.'], 500);
        }

        $path = $baseDir . DIRECTORY_SEPARATOR . $filename;

        // grava JSON bonito
        $ok = (bool) file_put_contents($path, json_encode($map, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        if (!$ok) {
            return response()->json(['message' => 'Falha ao escrever o arquivo.'], 500);
        }

        return response()->json(['ok' => true, 'path' => $path]);
    }
}
