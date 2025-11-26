<?php
declare(strict_types=1);

namespace SSync;

use SSync\Commands\AddCommand;
use SSync\Commands\CatCommand;
use SSync\Commands\DiffCommand;
use SSync\Commands\ListCommand;
use SSync\Commands\RemoveCommand;
use SSync\Commands\SyncCommand;
use Symfony\Component\Console\Application;

class SSync extends Application
{
    public function __construct()
    {
        parent::__construct('SSync', $this->version());
        $this->add(new AddCommand());
        $this->add(new CatCommand());
        $this->add(new DiffCommand());
        $this->add(new ListCommand());
        $this->add(new RemoveCommand());
        $this->add(new SyncCommand());
    }

    private function version(): string
    {
        $major = 0;
        $minor = 0;
        $patch = 0;

        $command = 'git log --pretty=format:%s 2>/dev/null';
        $output = [];
        $resultCode = 0;
        $cwd = getcwd();
        chdir(__DIR__);
        exec($command, $output, $resultCode);
        chdir($cwd);
        if ($resultCode !== 0) {
            return "$major.$minor.$patch"; // @codeCoverageIgnore
        }
        foreach (array_reverse($output) as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'fix')) {
                $patch++;
            }
            if (str_starts_with($line, 'feat')) {
                $minor++;
                $patch = 0;
            }
            if (preg_match('/^[a-z]+!:/', $line)) {
                $major++;
                $minor = 0;
                $patch = 0;
            }
        }
        return "$major.$minor.$patch";
    }
}
