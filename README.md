# Laravel Debug Loggers

Console route and SQL debug logging for Laravel applications.

## Installation

```bash
composer require bekunika/debug-loggers
```

Laravel package discovery will register the service provider automatically.

## Compatibility

- PHP 8.2+
- Laravel 11 or 12

## Configuration

Publish the config if you want to override defaults:

```bash
php artisan vendor:publish --tag=debug-loggers-config
```

Available options:

```php
return [
    'route' => [
        'enabled' => env('DEBUG_LOGGERS_ROUTE_ENABLED', false),
    ],
    'sql' => [
        'enabled' => env('DEBUG_LOGGERS_SQL_ENABLED', false),
    ],
];
```

## Environment Variables

```dotenv
DEBUG_LOGGERS_ROUTE_ENABLED=true
DEBUG_LOGGERS_SQL_ENABLED=true
```

## Testing

```bash
composer install
composer test
```

## Release

1. Push this package to its own public git repository.
2. Create a git tag such as `v1.0.0`.
3. Submit the repository URL on Packagist.
4. Enable Packagist auto-updates for the repository.
