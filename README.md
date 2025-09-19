<p align="center">
    <img src="assets/lang_monitor_logo.png" alt="Logo" height="200"/>
</p>

# Laravel Lang Monitor
<h2 style="display: flex; align-items: center; gap: 12px;">
    <svg width="32px" height="32px" viewBox="0 0 48.00 48.00" xmlns="http://www.w3.org/2000/svg" fill="#195087" stroke="#195087" stroke-width="0.00048000000000000007"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.288"></g><g id="SVGRepo_iconCarrier"> <title>new-star</title> <g id="Layer_2" data-name="Layer 2"> <g id="invisible_box" data-name="invisible box"> <rect width="48" height="48" fill="none"></rect> </g> <g id="icons_Q2" data-name="icons Q2"> <path d="M42.3,24l3.4-5.1a2,2,0,0,0,.2-1.7A1.8,1.8,0,0,0,44.7,16l-5.9-2.4-.5-5.9a2.1,2.1,0,0,0-.7-1.5,2,2,0,0,0-1.7-.3L29.6,7.2,25.5,2.6a2.2,2.2,0,0,0-3,0L18.4,7.2,12.1,5.9a2,2,0,0,0-1.7.3,2.1,2.1,0,0,0-.7,1.5l-.5,5.9L3.3,16a1.8,1.8,0,0,0-1.2,1.2,2,2,0,0,0,.2,1.7L5.7,24,2.3,29.1a2,2,0,0,0,1,2.9l5.9,2.4.5,5.9a2.1,2.1,0,0,0,.7,1.5,2,2,0,0,0,1.7.3l6.3-1.3,4.1,4.5a2,2,0,0,0,3,0l4.1-4.5,6.3,1.3a2,2,0,0,0,1.7-.3,2.1,2.1,0,0,0,.7-1.5l.5-5.9L44.7,32a2,2,0,0,0,1-2.9ZM18,31.1l-4.2-3.2L12.7,27h-.1l.6,1.4,1.7,4-2.1.8L9.3,24.6l2.1-.8L15.7,27l1.1.9h0a11.8,11.8,0,0,0-.6-1.3l-1.6-4.1,2.1-.9,3.5,8.6Zm3.3-1.3-3.5-8.7,6.6-2.6.7,1.8L20.7,22l.6,1.6L25.1,22l.7,1.7L22,25.2l.7,1.9,4.5-1.8.7,1.8Zm13.9-5.7-2.6-3.7-.9-1.5h-.1a14.7,14.7,0,0,1,.4,1.7l.8,4.5-2.1.9-5.9-7.7,2.2-.9,2.3,3.3,1.3,2h0a22.4,22.4,0,0,1-.4-2.3l-.7-4,2-.8L33.8,19,35,20.9h0s-.2-1.4-.4-2.4L34,14.6l2.1-.9,1.2,9.6Z"></path> </g> </g> </g></svg> 
    <span>Now UI Version available!</span>
</h2>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jonasschen/laravel-lang-monitor.svg?style=flat-square)](https://packagist.org/packages/jonasschen/laravel-lang-monitor)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/jonasschen/laravel-lang-monitor.svg?style=flat-square)](https://packagist.org/packages/jonasschen/laravel-lang-monitor)
![GitHub Actions](https://github.com/jonasschen/laravel-lang-monitor/actions/workflows/main.yml/badge.svg)

Automatically search for keys or phrases in your project that have no translations.

Using Laravel Lang Monitor, you can get all missing translations.
## Installation

You can install the package via composer:
```bash
composer require jonasschen/laravel-lang-monitor --dev
```

Publish the config, asset and view files using the artisan CLI tool:
```bash
php artisan vendor:publish --provider="Jonasschen\LaravelLangMonitor\LaravelLangMonitorServiceProvider"
```
This command will publish the following files:
- config/lang-monitor.php
- public/vendor/lang-monitor/css/lang-monitor.css
- public/vendor/lang-monitor/ico/lang-monitor.ico
- public/vendor/lang-monitor/images/lang_monitor_logo_small.png
- public/vendor/lang-monitor/js/lang-monitor.js
- resources/views/vendor/lang-monitor/monitor.blade.php

### Available configurations
- abort_if_directory_doesnt_exist (Default: false)
    - Abort if a directory doesn't exist: If any configured directories of the "directories_to_search" array do not exist, the scanning process will be aborted, otherwise only an alert it will be logged in the console; 
- abort_if_lang_file_doesnt_exist (Default: false)
    - Abort if a lang file doesn't exist: If any configured lang files of the "lang_files" array do not exist, the scanning process will be aborted, otherwise only an alert it will be logged in the console;
- scan_for_unused_translations (Default: true)
    - If enabled, will check if all key translations are in use and log unused keys;
- directories_to_search (Default: ['app', 'resources/views'])
    - Directories to search: A list of directories where the package will perform the scanning process;
- extensions_to_search (Default: ['php', 'js'])
    - Extensions to search: A list of file extensions that the package will consider to perform the scanning process;
- lang_files (Default: ['resources/lang/en.json'])
    - Lang files: A list of lang files where the package will try to search the translation keys;
- locale (Default en.utf8)
    - Locale: The locale of the main project language. It will be used to perform an improved sorting of the untranslated keys when exporting a result;
- middleware (Default: ['web'])
    - Middleware: Middleware stack wrapping all Lang Monitor routes. Adjust to control access to the UI.
- ui_prefix (Default: lang-monitor)
    - UI prefix: URL prefix where the Lang Monitor UI will be available. Example: "lang-monitor" → https://your-app.test/lang-monitor;

## Usage via browser
You can access the Lang Monitor UI by going to the URL below:
```
http://your-app.test/lang-monitor
```
You can customize the URL prefix by changing the "ui_prefix" option in the config file **"/config/lang-monitor.php"**.

## Usage via prompt
Use the command below, it is that easy!
```php
php artisan lang_monitor:scan
```

### Output example with missing translations
```
Key not found: [Nova senha] - Used in file [resources/views/auth/changepassword.blade.php:20]
Key not found: [Confirmar senha] - Used in file [resources/views/auth/changepassword.blade.php:22]
****************************************
*      LARAVEL LANG MONITOR REPORT     *
****************************************
* Found keys: 13564                    *
* Untranslated keys: 37                *
* Unique untranslated keys: 30         *
* Unused translations: 1474            *
****************************************

```

### Output example without missing translations
```
********************************************************
*       Great! All translations are working fine.      *
********************************************************
```

### Export untranslated keys to JSON file format
You can export a missing translation result for a file in a JSON format. Use the --export_missed_json_file option like this:
```php
php artisan lang_monitor:scan --export_missed_json_file=storage/logs/untranslateds.json
```

### Export untranslated keys to PHP file format
You can export a missing translation result for a file in a PHP format. Use the --export_missed_php_file option like this:
```php
php artisan lang_monitor:scan --export_missed_php_file=storage/logs/untranslateds.php
```

### Export untranslated keys to text file format
You can export a missing translation result for a file in a text format. Use the --export_missed_text_file option like this:
```php
php artisan lang_monitor:scan --export_missed_txt_file=storage/logs/untranslateds.txt
```

### Export unused keys to JSON file format
You can export unused keys result for a file in a JSON format. Use the --export_unused_json_file option like this:
```php
php artisan lang_monitor:scan --export_unused_json_file=storage/logs/unuseds.json
```

### Export unused keys to PHP file format
You can export unused keys result for a file in a PHP format. Use the --export_unused_php_file option like this:
```php
php artisan lang_monitor:scan --export_unused_php_file=storage/logs/unuseds.php
```

### Export unused keys to a text file format
You can export unused keys result for a file in a text format. Use the --export_unused_text_file option like this:
```php
php artisan lang_monitor:scan --export_unused_txt_file=storage/logs/unuseds.txt
```

### Translation function support
This package supports @lang(), __() and trans() functions.

### Lang file format support
This package supports .php files and .json files formats.

### Consider Sponsoring
Help me maintain this project, please consider looking at the [FUNDING](./.github/FUNDING.yml) file for more info.

<a href="https://bmc.link/jonasschen" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;" ></a>

#### BTC
![btc](https://github.com/jonasschen/laravel-lang-monitor/assets/31046817/2f69a4aa-4ee2-442e-aa1f-4a1c0cde217c)

#### ETH
![eth](https://github.com/jonasschen/laravel-lang-monitor/assets/31046817/41ca0d2f-e120-4733-a96b-ff7a34e1e4de)

### Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes.

### Testing
```bash
composer test
```

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security
If you discover any security-related issues, please email jonasschen@gmail.com instead of using the issue tracker. Please do not email any questions, open an issue if you have a question.

## Credits
-   [Jonas Schen](https://github.com/jonasschen)
-   [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
