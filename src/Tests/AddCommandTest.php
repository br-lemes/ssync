<?php
declare(strict_types=1);

namespace SSync\Tests;

use Exception;
use SSync\Traits\ConstTrait;

final class AddCommandTest extends AbstractCommandTestCase
{
    use ConstTrait;

    function testSuccess(): void
    {
        $args['config'] = 'test';
        $args['file'] = 'subdir/different.php';
        $test = $this->exec('add', $args);
        $this->assertStringStartsWith('Saved diff to: ', $test->getDisplay());
    }

    function testNoChanges(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No changes.');

        $args['config'] = 'test';
        $args['file'] = 'equal.php';
        $this->exec('add', $args);
    }

    function testComplete(): void
    {
        $this->assertCompleteContains('add', [''], ['test']);
        $this->assertCompleteEquals(
            'add',
            ['test', ''],
            ['different.php', 'equal.php', 'ignored.php', 'subdir/'],
        );
    }
}
