<?php

namespace Eventapo\DebugLoggers;

use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleWriter
{
    public function write(string $text, string $color = 'default'): void
    {
        $output = new ConsoleOutput;
        $output->writeln('<fg='.$color.';options=bold>'.$text.'</>');
        $output->writeln('------------------------------');
    }
}
