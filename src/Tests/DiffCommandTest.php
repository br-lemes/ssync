<?php
declare(strict_types=1);

namespace SSync\Tests;

use SSync\Traits\ConstTrait;
use Symfony\Component\Console\Command\Command;

final class DiffCommandTest extends AbstractCommandTestCase
{
    use ConstTrait;

    function testSuccess(): void
    {
        $args['config'] = 'test';
        $args['file'] = 'different.php';
        $test = $this->exec('diff', $args);
        $this->assertEquals(Command::SUCCESS, $test->getStatusCode());
    }

    function testNoFile(): void
    {
        $args['config'] = 'test';
        $args['file'] = 'nonexistent.php';
        $test = $this->exec('diff', $args);
        $this->assertNotEquals(Command::SUCCESS, $test->getStatusCode());
    }

    function testComplete(): void
    {
        $this->assertCompleteContains('diff', [''], ['test']);
        $this->assertCompleteEquals(
            'diff',
            ['test', ''],
            ['different.php', 'equal.php', 'ignored.php', 'subdir/'],
        );
    }
}
