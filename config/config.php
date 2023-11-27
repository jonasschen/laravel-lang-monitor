<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Abort if directory doesn't exists
    |--------------------------------------------------------------------------
    |
    | If any of the configured directories of the "directories_to_search" array
    | does not exist, the scanning process will be aborted, otherwise only an
    | alert it will be logged an in the console
    |
    */
    'abort_if_directory_doesnt_exists' => false,

    /*
    |--------------------------------------------------------------------------
    | Abort if lang file doesn't exists
    |--------------------------------------------------------------------------
    |
    | If any of the configured lang files of the "lang_files" array does not
    | exist, the scanning process will be aborted, otherwise only an alert it
    | will be logged an in the console
    |
    */
    'abort_if_lang_file_doesnt_exists' => false,

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
    | translations keys
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
    | The locale of main project language. It will be used to perform a
    | improved sorting of the untranslated keys when exporting result
    |
    */
    'locale' => 'en.utf8',

    'chat_gpt' => [
        'api_key' => '',
    ],
];
