<?php
declare(strict_types=1);

namespace SSync\Tests;

use Exception;
use SSync\Traits\ConstTrait;

final class ListCommandTest extends AbstractCommandTestCase
{
    use ConstTrait;

    function testSuccess(): void
    {
        $args['config'] = 'test';
        $test = $this->exec('ls', $args);
        $this->assertEquals(
            <<<END
            subdir/different.php
            different.php

            END
            ,
            $test->getDisplay(),
        );
    }

    function testNoDiffStore(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            sprintf(
                self::DIRECTORY_NOT_EXISTS,
                realpath(__DIR__ . '/../../config/error') . '/diffs',
            ),
        );

        $args['config'] = 'error';
        $this->exec('ls', $args);
    }

    function testComplete(): void
    {
        $this->assertCompleteContains('ls', [''], ['error', 'test']);
    }
}
