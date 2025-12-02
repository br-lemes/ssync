<?php
declare(strict_types=1);

namespace SSync\Tests;

use PHPUnit\Framework\TestCase;
use SSync\SSync;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandCompletionTester;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTestCase extends TestCase
{
    protected Application $application;

    function setUp(): void
    {
        parent::setUp();

        $this->application = new SSync();
    }

    function assertCompleteContains(
        string $name,
        array $input,
        array $values,
    ): void {
        $actual = $this->complete($name, $input);
        foreach ($values as $value) {
            $this->assertContains($value, $actual);
        }
    }

    function assertCompleteEquals(
        string $name,
        array $input,
        array $values,
    ): void {
        $actual = $this->complete($name, $input);
        $this->assertEquals($values, $actual);
    }

    function complete(string $name, array $input): array
    {
        $command = $this->application->find($name);
        $command->setApplication($this->application);

        $tester = new CommandCompletionTester($command);
        return $tester->complete($input);
    }

    function exec(string $name, array $args, array $inputs = []): CommandTester
    {
        $command = $this->application->find($name);
        $command->setApplication($this->application);

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($args);

        return $commandTester;
    }
}
