# Laravel Lang Monitor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jonasschen/laravel-lang-monitor.svg?style=flat-square)](https://packagist.org/packages/jonasschen/laravel-lang-monitor)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/jonasschen/laravel-lang-monitor.svg?style=flat-square)](https://packagist.org/packages/jonasschen/laravel-lang-monitor)
![GitHub Actions](https://github.com/jonasschen/laravel-lang-monitor/actions/workflows/main.yml/badge.svg)

Automatically search for keys or phrases in your project that have no translations.

Using Laravel Lang Monitor you can get all missing translations.
## Installation

You can install the package via composer:
```bash
composer require jonasschen/laravel-lang-monitor --dev
```

Publish the config file using the artisan CLI tool:
```bash
php artisan vendor:publish --provider="Jonasschen\LaravelLangMonitor\LaravelLangMonitorServiceProvider"
```

### Available configurations
- abort_if_directory_doesnt_exists (Default: false)
    - Abort if directory doesn't exists: If any of the configured directories of the "directories_to_search" array does not exist, the scanning process will be aborted, otherwise only an alert it will be logged an in the console;
- abort_if_lang_file_doesnt_exists (Default: false)
    - Abort if lang file doesn't exists: If any of the configured lang files of the "lang_files" array does not exist, the scanning process will be aborted, otherwise only an alert it will be logged an in the console;
- directories_to_search (Default: ['app', 'resources/views'])
    - Directories to search: A list of directories where the package will perform the scanning process;
- extensions_to_search (Default: ['php', 'js'])
    - Extensions to search: A list of file extensions that the package will consider to perform the scanning process;
- lang_files (Default: ['resources/lang/en.json'])
    - Lang files: A list of lang files where the package will try to search the translations keys;
- locale Default(en.utf8)
    - Locale: The locale of main project language. It will be used to perform a improved sorting of the untranslated keys when exporting result;

## Usage
Use the command below, it is that easy!
```php
php artisan lang_monitor:scan
```

### Output example with missing translations
```
Key not found: [Nova senha] - Used in file [resources/views/auth/changepassword.blade.php:20]
Key not found: [Confirmar senha] - Used in file [resources/views/auth/changepassword.blade.php:22]
********************************************************
*       Untranslated keys: 2 | Unique keys: 2        *
********************************************************
```

### Output example without missing translations
```
********************************************************
*       Great! All translations are working fine.      *
********************************************************
```

### Export untranslated keys to JSON file format
You can export missing translations result for a file in a JSON format. use the --export_json_file option like this:
```php
php artisan lang_monitor:scan --export_json_file=storage/logs/untranslateds.json
```

### Export untranslated keys to PHP file format
You can export missing translations result for a file in a PHP format. use the --export_php_file option like this:
```php
php artisan lang_monitor:scan --export_php_file=storage/logs/untranslateds.php
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
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

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
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
