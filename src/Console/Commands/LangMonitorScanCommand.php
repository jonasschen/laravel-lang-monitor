<?php

namespace Jonasschen\LaravelLangMonitor\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LangMonitorScanCommand extends Command
{
    private const PATTERNS = [
//        '/@lang\(\s*["\']([^"\']*)["\'][^)]*\)/',
//        '/trans\(\s*["\']([^"\']*)["\'][^)]*\)/',
//        '/__\(\s*["\']([^"\']*)["\'][^)]*\)/',
        "/@lang\((['\"])(.*?)\\1/",
        "/trans\((['\"])(.*?)\\1/",
        "/__\((['\"])(.*?)\\1/",
    ];

    protected $signature = 'lang_monitor:scan {--export_json_file= : [DEPRECATED] Equal to export_missed_json_file. Kept for compatibility} {--export_php_file= : [DEPRECATED] Equal to export_missed_php_file. Kept for compatibility} {--export_missed_json_file= : The filename where you wish to export the JSON file with missed translations} {--export_missed_php_file= : The filename where you wish to export the PHP file with missed translations} {--export_missed_txt_file= : The filename where you wish to export the text file with missed translations} {--export_unused_json_file= : The filename where you wish to export the JSON file with unused keys} {--export_unused_php_file= : The filename where you wish to export the PHP file with unused keys} {--export_unused_txt_file= : The filename where you wish to export the text file with unused keys}';

    protected $description = 'Searches for all @lang(), __() and trans() keys in all configured files and check if them exists in the configured lang files.';
    private array $directoriesToSearch;
    private array $extensionsToSearch;
    private array $langFiles;
    private array $codeFiles;
    private array $translationsData;
    private bool $abortIfDirectoryDoesntExists;
    private bool $abortIfLangFileDoesntExists;
    private int $totalKeys;
    private ?string $exportMissedJsonFile;
    private ?string $exportMissedPhpFile;
    private ?string $exportMissedTxtFile;
    private ?string $exportUnusedJsonFile;
    private ?string $exportUnusedPhpFile;
    private ?string $exportUnusedTxtFile;

    public function loadConfigs(): void
    {
        // Get the directories and JSON files from the arguments
        $this->directoriesToSearch = config('lang-monitor.directories_to_search', []);
        $this->extensionsToSearch = config('lang-monitor.extensions_to_search', []);
        $this->langFiles = config('lang-monitor.lang_files', []);
        $this->abortIfDirectoryDoesntExists = config('lang-monitor.abort_if_directory_doesnt_exists', false);
        $this->abortIfLangFileDoesntExists = config('lang-monitor.abort_if_lang_file_doesnt_exists', false);

        $this->exportMissedJsonFile = $this->option('export_json_file') ?: null;
        if (!$this->exportMissedJsonFile) {
            $this->exportMissedJsonFile = $this->option('export_missed_json_file') ?: null;
        }
        $this->exportMissedPhpFile = $this->option('export_php_file') ?: null;
        if (!$this->exportMissedPhpFile) {
            $this->exportMissedPhpFile = $this->option('export_missed_php_file') ?: null;
        }
        $this->exportMissedTxtFile = $this->option('export_missed_txt_file') ?: null;

        $this->exportUnusedJsonFile = $this->option('export_unused_json_file') ?: null;
        $this->exportUnusedPhpFile = $this->option('export_unused_php_file') ?: null;
        $this->exportUnusedTxtFile = $this->option('export_unused_txt_file') ?: null;
    }

    public function handle(): int
    {
        $this->loadConfigs();
        $this->getCodeFiles();
        $this->getTranslationsData();

        $missingDirectoriesToSearch = $this->checkDirectoriesExist();
        if (is_null($missingDirectoriesToSearch)) {
            return 1;
        }

        $missingLangFiles = $this->checkFilesExist();
        if (is_null($missingLangFiles)) {
            return 1;
        }

        $this->totalKeys = 0;

        $keysNotFound = $this->getKeysNotFound();
        $unusedTranslations = $this->getUnusedTranslations();

        if (0 == count($keysNotFound)) {
            $this->info("Great! All translations are working fine.\n");
        } else {
            if ($missingDirectoriesToSearch) {
                foreach ($missingDirectoriesToSearch as $directory) {
                    $this->alert("Directory [{$directory}] does not exist and it was ignored.");
                }
            }

            if ($missingLangFiles) {
                foreach ($missingLangFiles as $langFile) {
                    $this->alert("Lang file [{$langFile}] does not exist and it was ignored.");
                }
            }

            $keysNotFoundUnique = array_unique($keysNotFound);

            $this->printReport(count($keysNotFound), count($keysNotFoundUnique), count($unusedTranslations));
            $this->exportFiles($keysNotFoundUnique, $unusedTranslations);
        }

        //$this->drawFlag();
        return 0;
    }

    private function sortArray(array $array): array
    {
        setlocale(LC_ALL, config('lang-monitor.locale', ''));
        asort($array, SORT_LOCALE_STRING);

        return $array;
    }

    private function sortArrayMulti(array $array): array
    {
        setlocale(LC_ALL, config('lang-monitor.locale', ''));

        usort($array, function($a, $b) {
            return strcmp($a['key'], $b['key']);
        });

        return $array;
    }

    private function exportFiles(array $keysNotFoundUnique, array $unusedTranslations): void
    {
        $this->exportMissedFiles($keysNotFoundUnique);
        $this->exportUnusedFiles($unusedTranslations);
    }

    private function exportMissedFiles(array $keysNotFoundUnique): void
    {
        $keysNotFoundUnique = $this->sortArray($keysNotFoundUnique);
        $this->exportMissedJsonFile($keysNotFoundUnique);
        $this->exportMissedPhpFile($keysNotFoundUnique);
        $this->exportMissedTxtFile($keysNotFoundUnique);
    }

    private function exportUnusedFiles(array $unusedTranslations): void
    {
        $unusedTranslations = $this->sortArrayMulti($unusedTranslations);
        $this->exportUnusedJsonFile($unusedTranslations);
        $this->exportUnusedPhpFile($unusedTranslations);
        $this->exportUnusedTxtFile($unusedTranslations);
    }

    private function exportMissedJsonFile(array $keysNotFound): void
    {
        if ($this->exportMissedJsonFile) {
            $myFile = fopen($this->exportMissedJsonFile, 'w') or exit('Unable to open JSON file!');
            fwrite($myFile, "{\n");
            $ifFirst = true;
            foreach ($keysNotFound as $key) {
                if (!$ifFirst) {
                    fwrite($myFile, ",\n");
                }
                $line = sprintf('    "%s": ""', $key);
                fwrite($myFile, $line);
                $ifFirst = false;
            }
            fwrite($myFile, "\n}\n");
            fclose($myFile);

            $this->info("Untranslated keys exported to [{$this->exportMissedJsonFile}] as JSON file.\n");
        }
    }

    private function exportUnusedJsonFile(array $unusedKeys): void
    {
        if ($this->exportUnusedJsonFile) {
            $myFile = fopen($this->exportUnusedJsonFile, 'w') or exit('Unable to open JSON file!');
            fwrite($myFile, "{\n");
            $ifFirst = true;
            foreach ($unusedKeys as $key) {
                if (!$ifFirst) {
                    fwrite($myFile, ",\n");
                }
                $line = sprintf('    "%s": "FILE: %s"', $key['key'], $key['file']);
                fwrite($myFile, $line);
                $ifFirst = false;
            }
            fwrite($myFile, "\n}\n");
            fclose($myFile);

            $this->info("Unused keys exported to [{$this->exportUnusedJsonFile}] as JSON file.\n");
        }
    }

    private function exportMissedPhpFile(array $keysNotFound): void
    {
        if ($this->exportMissedPhpFile) {
            $myFile = fopen($this->exportMissedPhpFile, 'w') or exit('Unable to open PHP file!');
            fwrite($myFile, "<?php\n\nreturn [\n");
            foreach ($keysNotFound as $key) {
                $line = sprintf("    '%s' => '',\n", $key);
                fwrite($myFile, $line);
            }
            fwrite($myFile, "];\n");
            fclose($myFile);

            $this->info("Untranslated keys exported to [{$this->exportMissedPhpFile}] as PHP file.\n");
        }
    }

    private function exportUnusedPhpFile(array $unusedKeys): void
    {
        if ($this->exportUnusedPhpFile) {
            $myFile = fopen($this->exportUnusedPhpFile, 'w') or exit('Unable to open PHP file!');
            fwrite($myFile, "<?php\n\nreturn [\n");
            foreach ($unusedKeys as $key) {
                $line = sprintf("    '%s' => 'FILE: %s',\n", $key['key'], $key['file']);
                fwrite($myFile, $line);
            }
            fwrite($myFile, "];\n");
            fclose($myFile);

            $this->info("Untranslated keys exported to [{$this->exportUnusedPhpFile}] as PHP file.\n");
        }
    }

    private function exportMissedTxtFile(array $keysNotFound): void
    {
        if ($this->exportMissedTxtFile) {
            $myFile = fopen($this->exportMissedTxtFile, 'w') or exit('Unable to open text file!');
            foreach ($keysNotFound as $key) {
                $line = sprintf("%s\n", $key);
                fwrite($myFile, $line);
            }
            fclose($myFile);

            $this->info("Untranslated keys exported to [{$this->exportMissedTxtFile}] as text file.\n");
        }
    }

    private function exportUnusedTxtFile(array $unusedKeys): void
    {
        if ($this->exportUnusedTxtFile) {
            $myFile = fopen($this->exportUnusedTxtFile, 'w') or exit('Unable to open text file!');
            foreach ($unusedKeys as $key) {
                $line = sprintf("%s -> FILE: %s\n", $key['key'], $key['file']);
                fwrite($myFile, $line);
            }
            fclose($myFile);

            $this->info("Untranslated keys exported to [{$this->exportUnusedTxtFile}] as text file.\n");
        }
    }

    /**
     * Check if all directories exist, exit with error message if not
     */
    private function checkDirectoriesExist(): ?array
    {
        $missingDirectoriesToSearch = [];
        foreach ($this->directoriesToSearch as $directory) {
            if (!file_exists($directory)) {
                if ($this->abortIfDirectoryDoesntExists) {
                    $this->error("Directory [{$directory}] does not exist.");

                    return null;
                }
                $missingDirectoriesToSearch[] = $directory;
            }
        }

        return $missingDirectoriesToSearch;
    }

    /**
     * Check if all files exist, exit with error message if not
     */
    private function checkFilesExist(): ?array
    {
        $missingLangFiles = [];
        foreach ($this->langFiles as $langFile) {
            if (!file_exists($langFile)) {
                if ($this->abortIfLangFileDoesntExists) {
                    $this->error("Lang file [{$langFile}] does not exist.");

                    return null;
                }
                $missingLangFiles[] = $langFile;
            }
        }

        return $missingLangFiles;
    }

    /**
     * Get a list of all files in the directories and subdirectories
     */
    private function getCodeFiles(): void
    {
        $files = [];
        $extensionsToSearch = implode('|', $this->extensionsToSearch);
        foreach ($this->directoriesToSearch as $directory) {
            if (file_exists($directory)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
                foreach ($iterator as $file) {
                    if ($file->isFile() && preg_match('/\.(' . $extensionsToSearch . ')$/i', $file->getPathname())) {
                        $files[] = $file->getPathname();
                    }
                }
            }
        }

        $this->codeFiles = $files;
    }

    /**
     * Read the contents of the JSON files and convert to associative arrays
     */
    private function getTranslationsData(): void
    {
        $data = [];
        foreach ($this->langFiles as $langFile) {
            if (file_exists($langFile)) {
                if (Str::endsWith($langFile, '.json')) {
                    $json = json_decode(file_get_contents($langFile), true);
                } else {
                    $json = include $langFile;
                }

                $data[] = [
                    'data' => $json,
                    'file' => $langFile,
                ];
            }
        }

        $this->translationsData = $data;
    }

    private function getKeysNotFound(): array
    {
        $keysNotFound = [];

        // Iterate over each file
        foreach ($this->codeFiles as $file) {
            // Read the contents of the file into an array of lines
            $lines = file($file);

            // Iterate over each search pattern
            foreach (self::PATTERNS as $pattern) {
                // Search for keys with the current search pattern in each line of the file
                $lineNumber = 0;
                foreach ($lines as $line) {
                    $lineNumber++;
                    preg_match_all($pattern, trim($line), $matches);
                    if (!empty($matches[2])) {
                        $this->totalKeys++;
                    }

                    // Iterate over each key found
                    foreach ($matches[2] as $match) {
                        $found = false;
                        foreach ($this->translationsData as $translationData) {
                            // Check if the key has already been found in the JSON file before searching for it
                            if (isset($translationData['data'][$match])) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $keysNotFound[] = $match;
                            $this->line("Key not found: [{$match}] - Used in file [{$file}:{$lineNumber}]");
                        }
                    }
                }
            }
        }

        return $keysNotFound;
    }

    private function getUnusedTranslations(): array
    {
        $foundKeys = [];

        // Iterate over each file
        foreach ($this->codeFiles as $file) {
            // Read the contents of the file into an array of lines
            $lines = file($file);

            // Iterate over each search pattern
            foreach (self::PATTERNS as $pattern) {
                // Search for keys with the current search pattern in each line of the file
                foreach ($lines as $line) {
                    preg_match_all($pattern, trim($line), $matches);
                    if (!empty($matches[2])) {
                        $foundKeys = array_merge($foundKeys, $matches[2]);
                    }
                }
            }
        }

        $unusedTranslations = [];
        foreach ($this->translationsData as $item) {
            foreach ($item['data'] as $key => $value) {
                if (!in_array($key, $foundKeys)) {
                    $unusedTranslations[] = [
                        'key' => $key,
                        'file' => $item['file'],
                    ];
                }
            }
        }

        foreach ($unusedTranslations as $item) {
            $this->line("Unused translation: [{$item['key']}] - Used in file [{$item['file']}]");
        }

        return $unusedTranslations;
    }

    private function printReport(
        int $totalKeysNotFound,
        int $totalKeysNotFoundUnique,
        int $totalUnusedTranslations,
    ): void {
        $this->comment(str_repeat('*', 40));
        $this->comment('*      LARAVEL LANG MONITOR REPORT     *');
        $this->comment(str_repeat('*', 40));
        $this->writeLn('Found keys: ' . $this->totalKeys);
        $this->writeLn('Untranslated keys: ' . $totalKeysNotFound);
        $this->writeLn('Unique untranslated keys: ' . $totalKeysNotFoundUnique);
        $this->writeLn('Unused translations: ' . $totalUnusedTranslations);

        $this->comment(str_repeat('*', 40));
    }

    private function writeLn(string $string): void
    {
        $length = Str::length(strip_tags($string));
        $spaces = str_repeat(' ', (37 - $length));
        $this->comment('* ' . $string . $spaces . '*');
    }

    private function drawFlag(): void
    {
        $this->output->newLine();

        $columns = 30;
        $lines = 11;
        $greenColumns = $columns;
        $redColumns = $columns;
        $yellowColumns = $columns;

        for ($i = 0; $i < $lines; $i++) {
            if ($greenColumns > 0) {
                $this->output->write('<bg=green>' . str_repeat(' ', $greenColumns) . '</>');

                if ($greenColumns < $columns) {
                    $this->output->write("<bg=red>" . str_repeat(' ', $redColumns-$greenColumns) . "</>");
                }

                $greenColumns = $greenColumns - 6;
            } elseif ($redColumns >= 0) {
                if ($redColumns > 0) {
                    if ($i == 5) {
                        $this->output->write("<bg=red;fg=bright-white>      PRAY FOR RS/BRAZIL      </>");
                    } else {
                        $this->output->write("<bg=red>" . str_repeat(' ', $redColumns) . "</>");
                    }
                }

                if ($redColumns < $columns) {
                    $this->output->write("<bg=yellow>" . str_repeat(' ', $yellowColumns-$redColumns) . "</>");
                }

                $redColumns = $redColumns - 6;
            }

            $this->output->newLine();
        }
    }
}
