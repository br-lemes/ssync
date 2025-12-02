<?php
declare(strict_types=1);

namespace SSync\Tests;

use Exception;
use SSync\Traits\ConstTrait;
use Symfony\Component\Console\Command\Command;

final class CatCommandTest extends AbstractCommandTestCase
{
    use ConstTrait;

    function testSuccess(): void
    {
        $args['config'] = 'test';
        $args['file'] = 'different.php';
        $test = $this->exec('cat', $args);
        $this->assertEquals(Command::SUCCESS, $test->getStatusCode());
    }

    function testNoDiffStore(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::DIFF_NOT_FOUND);

        $args['config'] = 'test';
        $args['file'] = 'equal.php';
        $this->exec('cat', $args);
    }

    function testComplete(): void
    {
        $this->assertCompleteContains('cat', [''], ['test']);
        $this->assertCompleteEquals(
            'cat',
            ['test', ''],
            ['different.php', 'subdir/'],
        );
    }
}
