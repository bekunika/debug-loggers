<?php

namespace Eventapo\DebugLoggers\Tests;

use Eventapo\DebugLoggers\ConsoleWriter;
use Eventapo\DebugLoggers\Tests\Fixtures\SpyConsoleWriter;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

class DebugLoggersServiceProviderTest extends TestCase
{
    public function test_it_registers_the_console_writer_singleton(): void
    {
        $writer = $this->app->make(ConsoleWriter::class);

        $this->assertInstanceOf(ConsoleWriter::class, $writer);
        $this->assertSame($writer, $this->app->make(ConsoleWriter::class));
    }

    public function test_it_logs_matched_routes_when_enabled(): void
    {
        config()->set('debug-loggers.route.enabled', true);

        $spy = new SpyConsoleWriter;
        $this->app->instance(ConsoleWriter::class, $spy);

        Route::get('/health', fn () => 'ok');

        $request = Request::create('/health', 'GET');
        $route = app('router')->getRoutes()->match($request);

        Event::dispatch(new RouteMatched('web', $route, $request));

        $this->assertCount(1, $spy->messages);
        $this->assertStringContainsString('GET:', $spy->messages[0]['text']);
        $this->assertStringContainsString('/health', $spy->messages[0]['text']);
    }

    public function test_it_logs_transaction_events_when_enabled(): void
    {
        config()->set('debug-loggers.sql.enabled', true);

        $spy = new SpyConsoleWriter;
        $this->app->instance(ConsoleWriter::class, $spy);

        Event::dispatch(new TransactionBeginning(DB::connection()));

        $this->assertCount(1, $spy->messages);
        $this->assertSame('START TRANSACTION', $spy->messages[0]['text']);
        $this->assertSame('magenta', $spy->messages[0]['color']);
    }

    public function test_it_logs_queries_when_enabled(): void
    {
        config()->set('debug-loggers.sql.enabled', true);

        $spy = new SpyConsoleWriter;
        $this->app->instance(ConsoleWriter::class, $spy);

        DB::select('select 1 as value');

        $this->assertNotEmpty($spy->messages);
        $this->assertTrue(collect($spy->messages)->contains(function (array $message): bool {
            return str_starts_with(strtolower($message['text']), 'select');
        }));
    }
}
