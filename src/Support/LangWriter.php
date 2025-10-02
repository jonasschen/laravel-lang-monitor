<?php

namespace Jonasschen\LaravelLangMonitor\Support;

class LangWriter
{
    public function __construct(
        protected string $langBasePath,
        protected string $targetLocale = 'pt-BR',
    ) {}

    /**
     * Recebe um objeto { key: value } e escreve em resources/lang/{locale}.json
     */
    public function mergeMap(array $map): void
    {
        $file = $this->langBasePath . '/' . $this->targetLocale . '.json';
        $current = file_exists($file)
            ? json_decode(file_get_contents($file), true) ?: []
            : [];

        foreach ($map as $key => $value) {
            $current[$key] = (string) ($value ?? '');
        }

        ksort($current);
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0o775, true);
        }
        file_put_contents($file, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
