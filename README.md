# Laravel Lang Monitor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jonasschen/laravel-lang-monitor.svg?style=flat-square)](https://packagist.org/packages/jonasschen/laravel-lang-monitor)
[![Total Downloads](https://img.shields.io/packagist/dt/jonasschen/laravel-lang-monitor.svg?style=flat-square)](https://packagist.org/packages/jonasschen/laravel-lang-monitor)
![GitHub Actions](https://github.com/jonasschen/laravel-lang-monitor/actions/workflows/main.yml/badge.svg)

![Laravel translation checker](https://banners.beyondco.de/Laravel%20translation%20checker.png?theme=light&packageManager=composer+require&packageName=jonasschen%2Flaravel-lang-monitor&pattern=architect&style=style_1&description=Use+the+laravel+translation+checker+and+get+direct+feedback+where+and+what+translations+you+are+missing%21&md=1&showWatermark=0&fontSize=100px&images=globe)

Ever feel that you are missing translations in some languages you support? Get users emailing you about weird strings on their screen?

Use the laravel translation checker and get direct feedback where and what translations you are missing!
## Installation

You can install the package via composer:

```bash
composer require jonasschen/laravel-lang-monitor
```

## Usage
Use the command below, it is that easy!
```php
php artisan lang_monitor:scan
```

### Output example with missing translations
```
Word not found: [Nova senha] - Used in file [resources/views/auth/changepassword.blade.php]
Word not found: [Confirmar senha] - Used in file [resources/views/auth/changepassword.blade.php]
********************************************************
*       Untranslated words: 2 | Unique words: 2        *
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
This package supports booth @lang() and __() functions.

### Lang file format support
This package supports .php files and .json files formats.

### Running in github actions?
```
  translations:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mbstring, intl
          ini-values: post_max_size=256M, max_execution_time=180
          coverage: xdebug
          tools: php-cs-fixer, phpunit
      - name: Install Dependencies
        run: composer install -q --no-interaction --no-scripts
      - name: Run lang monitor
        run: php artisan lang_monitor:scan
```

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
