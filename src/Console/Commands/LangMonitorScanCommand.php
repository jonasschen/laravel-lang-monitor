<?php

namespace Jonasschen\LaravelLangMonitor\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LangMonitorScanCommand extends Command
{
    protected $signature = 'lang_monitor:scan {--export_json_file= : The filename where you wish to export the JSON file} {--export_php_file= : The filename where you wish to export the PHP file}';

    protected $description = 'Searches for all @lang() and __() keys in all configured files and check if them exists in the configured lang files.';

    public function handle()
    {
        // Define the search patterns
        $patterns = [
            '/@lang\([\'"]([^\'"]+)[\'"]\)/',
            '/__\([\'"]([^\'"]+)[\'"]\)/',
        ];

        // Get the directories and JSON files from the arguments
        $directoriesToSearch = config('lang-monitor.directories_to_search', []);
        $extensionsToSearch = config('lang-monitor.extensions_to_search', []);
        $langFiles = config('lang-monitor.lang_files', []);
        $abortIfDirectoryDoesntExists = config('lang-monitor.abort_if_directory_doesnt_exists', false);
        $abortIfLangFileDoesntExists = config('lang-monitor.abort_if_lang_file_doesnt_exists', false);
        $exportJsonFile = false;
        $exportPhpFile = false;

        $exportJsonFile = $this->option('export_json_file') ?: null;
        $exportPhpFile = $this->option('export_php_file') ?: null;
        $missingDirectoriesToSearch = [];
        $missingLangFiles = [];

        // Check if all directories exist, exit with error message if not
        foreach ($directoriesToSearch as $directory) {
            if (!file_exists($directory)) {
                if ($abortIfDirectoryDoesntExists) {
                    $this->error("Directory [$directory] does not exist.");
                    return 1;
                } else {
                    $missingDirectoriesToSearch[] = $directory;
                }
            }
        }

        // Check if all files exist, exit with error message if not
        foreach ($langFiles as $langFile) {
            if (!file_exists($langFile)) {
                if ($abortIfLangFileDoesntExists) {
                    $this->error("Lang file [$langFile] does not exist.");
                    return 1;
                } else {
                    $missingLangFiles[] = $langFile;
                }
            }
        }

        // Get a list of all files in the directories and subdirectories
        $files = array();
        $extensionsToSearch = implode('|', $extensionsToSearch);
        foreach ($directoriesToSearch as $directory) {
            if (file_exists($directory)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
                foreach ($iterator as $file) {
                    if ($file->isFile() && preg_match('/\.(' . $extensionsToSearch . ')$/i', $file->getPathname())) {
                        $files[] = $file->getPathname();
                    }
                }
            }
        }

        // Read the contents of the JSON files and convert to associative arrays
        $jsonData = array();
        foreach ($langFiles as $langFile) {
            if (file_exists($langFile)) {
                if (Str::endsWith($langFile, '.json')) {
                    $json = json_decode(file_get_contents($langFile), true);
                } else {
                    $json = include($langFile);
                }

                $jsonData = array_merge($jsonData, $json);
            }
        }

        $keysNotFound = [];
        // Iterate over each file
        foreach ($files as $file) {
            // Read the contents of the file into an array of lines
            $lines = file($file);

            // Iterate over each search pattern
            foreach ($patterns as $pattern) {
                // Search for keys with the current search pattern in each line of the file
                $lineNumber = 0;
                foreach ($lines as $line) {
                    $lineNumber++;
                    preg_match_all($pattern, $line, $matches);

                    // Iterate over each key found
                    foreach ($matches[1] as $match) {
                        // Check if the key has already been found in the JSON file before searching for it
                        if (!isset($jsonData[$match])) {
                            $keysNotFound[] = $match;
                            $this->line("Key not found: [{$match}] - Used in file [{$file}:{$lineNumber}]");
                        }
                    }
                }
            }
        }

        if (count($keysNotFound) == 0) {
            $this->info("Great! All translations are working fine.\n");
        } else {
            if ($missingDirectoriesToSearch) {
                foreach ($missingDirectoriesToSearch as $directory) {
                    $this->alert("Directory [$directory] does not exist and it was ignored.");
                }
            }

            if ($missingLangFiles) {
                foreach ($missingLangFiles as $langFile) {
                    $this->alert("Lang file [$langFile] does not exist and it was ignored.");
                }
            }

            $keysNotFoundUnique = array_unique($keysNotFound);

            $this->alert(
                "Untranslated keys: " . count($keysNotFound) . " | " .
                "Unique keys: " . count($keysNotFoundUnique)
            );
            $keysNotFoundUnique = $this->sortArray($keysNotFoundUnique);
            if ($exportJsonFile) {
                $this->exportJsonFile($exportJsonFile, $keysNotFoundUnique);
            }

            if ($exportPhpFile) {
                $this->exportPhpFile($exportPhpFile, $keysNotFoundUnique);
            }
        }

        return 0;
    }

    /**
     * @param $array
     * @return array
     */
    private function sortArray($array): array
    {
        setlocale(LC_ALL, config('lang-monitor.locale', ''));
        asort($array, SORT_LOCALE_STRING);
        return $array;
    }

    /**
     * @param string $exportJsonFile
     * @param array $keysNotFound
     * @return void
     */
    private function exportJsonFile(string $exportJsonFile, array $keysNotFound): void
    {
        $myFile = fopen($exportJsonFile, "w") or die("Unable to open JSON file!");
        fwrite($myFile, "{\n");
        $ifFirst = true;
        foreach ($keysNotFound as $key) {
            if (!$ifFirst) {
                fwrite($myFile, ",\n");
            }
            $line = sprintf("    \"%s\": \"\"", $key);
            fwrite($myFile, $line);
            $ifFirst = false;
        }
        fwrite($myFile, "\n}\n");
        fclose($myFile);

        $this->info("Untranslated keys exported to [{$exportJsonFile}] as JSON file.\n");
    }

    /**
     * @param string $exportPhpFile
     * @param array $keysNotFound
     * @return void
     */
    private function exportPhpFile(string $exportPhpFile, array $keysNotFound): void
    {
        $myFile = fopen($exportPhpFile, "w") or die("Unable to open PHP file!");
        fwrite($myFile, "<?php\n\nreturn [\n");
        foreach ($keysNotFound as $key) {
            $line = sprintf("    '%s' => '',\n", $key);
            fwrite($myFile, $line);
        }
        fwrite($myFile, "];\n");
        fclose($myFile);

        $this->info("Untranslated keys exported to [{$exportPhpFile}] as PHP file.\n");
    }
}
