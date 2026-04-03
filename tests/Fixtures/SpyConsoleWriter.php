<?php

namespace Eventapo\DebugLoggers\Tests\Fixtures;

use Eventapo\DebugLoggers\ConsoleWriter;

class SpyConsoleWriter extends ConsoleWriter
{
    /**
     * @var array<int, array{text: string, color: string}>
     */
    public array $messages = [];

    public function write(string $text, string $color = 'default'): void
    {
        $this->messages[] = [
            'text' => $text,
            'color' => $color,
        ];
    }
}
