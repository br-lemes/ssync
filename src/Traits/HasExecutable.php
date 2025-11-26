<?php
declare(strict_types=1);

namespace SSync\Traits;

use Symfony\Component\Process\Process;

trait HasExecutable
{
    function hasExecutable(string $executable): bool
    {
        $process = new Process(['which', $executable]);
        $process->run();
        return $process->isSuccessful();
    }
}
