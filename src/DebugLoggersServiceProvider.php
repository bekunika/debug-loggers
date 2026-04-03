<?php

namespace Eventapo\DebugLoggers;

use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class DebugLoggersServiceProvider extends ServiceProvider
{
    private const CONFIG_NAME = 'debug-loggers';

    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), self::CONFIG_NAME);
        $this->app->singleton(ConsoleWriter::class);
    }

    public function boot(): void
    {
        $this->publishes([
            $this->configPath() => config_path(self::CONFIG_NAME.'.php'),
        ], self::CONFIG_NAME.'-config');

        $this->registerRouteLogger();
        $this->registerSqlLogger();
    }

    private function registerRouteLogger(): void
    {
        Event::listen(RouteMatched::class, function (RouteMatched $routeMatched): void {
            if (! $this->routeLoggingEnabled()) {
                return;
            }

            $message = sprintf(
                '%s:%s => %s',
                $routeMatched->request->getMethod(),
                $routeMatched->request->getUri(),
                $routeMatched->route->getActionName()
            );

            $this->consoleWriter()->write($message);
        });
    }

    private function registerSqlLogger(): void
    {
        Event::listen(TransactionBeginning::class, function (): void {
            if (! $this->sqlLoggingEnabled()) {
                return;
            }

            $this->writeSqlMessage('START TRANSACTION');
        });

        Event::listen(TransactionRolledBack::class, function (): void {
            if (! $this->sqlLoggingEnabled()) {
                return;
            }

            $this->writeSqlMessage('ROLLBACK');
        });

        Event::listen(TransactionCommitted::class, function (): void {
            if (! $this->sqlLoggingEnabled()) {
                return;
            }

            $this->writeSqlMessage('COMMIT');
        });

        DB::listen(function (QueryExecuted $query): void {
            if (! $this->sqlLoggingEnabled()) {
                return;
            }

            try {
                $addSlashes = str_replace('?', "'?'", $query->sql);
                $formattedQuery = str_replace('?', '%s', $addSlashes);

                if (! is_string($formattedQuery)) {
                    return;
                }

                $fullQuery = vsprintf($formattedQuery, $query->bindings);
                $this->writeSqlMessage($fullQuery);
            } catch (\Throwable) {
            }
        });
    }

    private function writeSqlMessage(string $query): void
    {
        $this->consoleWriter()->write($query, $this->detectSqlQueryColor($query));
    }

    private function detectSqlQueryColor(string $query): string
    {
        if (preg_match('/^((CREATE)|(ALTER)|(DROP)|(START)|(ROLLBACK)|(COMMIT))/i', $query)) {
            return 'magenta';
        }

        if (preg_match('/^SELECT/i', trim($query))) {
            return 'cyan';
        }

        if (preg_match('/^INSERT/i', trim($query))) {
            return 'green';
        }

        if (preg_match('/^UPDATE/i', trim($query))) {
            return 'yellow';
        }

        if (preg_match('/^DELETE/i', trim($query))) {
            return 'red';
        }

        return 'default';
    }

    private function routeLoggingEnabled(): bool
    {
        return (bool) config(self::CONFIG_NAME.'.route.enabled', false);
    }

    private function sqlLoggingEnabled(): bool
    {
        return (bool) config(self::CONFIG_NAME.'.sql.enabled', false);
    }

    private function consoleWriter(): ConsoleWriter
    {
        return $this->app->make(ConsoleWriter::class);
    }

    private function configPath(): string
    {
        return __DIR__.'/../config/'.self::CONFIG_NAME.'.php';
    }
}
