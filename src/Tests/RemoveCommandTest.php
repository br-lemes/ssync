<?php
declare(strict_types=1);

namespace SSync\Tests;

use Exception;
use SSync\Traits\ConstTrait;

final class RemoveCommandTest extends AbstractCommandTestCase
{
    use ConstTrait;

    function testSuccess(): void
    {
        $args['config'] = 'test';
        $args['file'] = 'subdir/different.php';
        $test = $this->exec('rm', $args);
        $this->assertStringStartsWith('Removed diff: ', $test->getDisplay());
    }

    function testNotFound(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(self::DIFF_NOT_FOUND);
        $args['config'] = 'test';
        $args['file'] = 'nonexistent.php';
        $this->exec('rm', $args);
    }

    function testComplete(): void
    {
        $this->assertCompleteContains('rm', [''], ['test']);
        $this->assertCompleteEquals(
            'rm',
            ['test', ''],
            ['different.php', 'subdir/'],
        );
    }
}
