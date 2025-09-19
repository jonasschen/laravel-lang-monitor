<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Abort if directory doesn't exists
    |--------------------------------------------------------------------------
    |
    | If any configured directories of the "directories_to_search" array
    | do not exist, the scanning process will be aborted, otherwise only an
    | alert it will be logged in the console
    |
    */
    'abort_if_directory_doesnt_exist' => false,

    /*
    |--------------------------------------------------------------------------
    | Abort if lang file doesn't exists
    |--------------------------------------------------------------------------
    |
    | If any configured lang files of the "lang_files" array do not exist,
    | the scanning process will be aborted, otherwise only an alert it
    | will be logged in the console
    |
    */
    'abort_if_lang_file_doesnt_exist' => false,

    /*
    |--------------------------------------------------------------------------
    | Scan for unused translations
    |--------------------------------------------------------------------------
    |
    | If enabled, will check if all key translations are in use and log unused
    | keys
    |
    */
    'scan_for_unused_translations' => true,

    /*
    |--------------------------------------------------------------------------
    | Directories to search
    |--------------------------------------------------------------------------
    |
    | A list of directories where the package will perform the scanning process
    |
    */
    'directories_to_search' => [
        'app',
        'resources/views',
    ],

    /*
    |--------------------------------------------------------------------------
    | Extensions to search
    |--------------------------------------------------------------------------
    |
    | A list of file extensions that the package will consider to perform the
    | scanning process
    |
    */
    'extensions_to_search' => [
        'php',
        'js',
    ],

    /*
    |--------------------------------------------------------------------------
    | Lang files
    |--------------------------------------------------------------------------
    |
    | A list of lang files where the package will try to search the
    | translation keys
    |
    */
    'lang_files' => [
        'resources/lang/en.json',
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | The locale of the main project language. It will be used to perform a
    | improved sorting of the untranslated keys when exporting a result
    |
    */
    'locale' => 'en.utf8',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware stack wrapping all Lang Monitor routes.
    | Adjust to control access to the UI.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | UI Prefix
    |--------------------------------------------------------------------------
    | URL prefix where the Lang Monitor UI will be available.
    | Example: "lang-monitor" → https://your-app.test/lang-monitor
    */
    'ui_prefix' => 'lang-monitor',
];
