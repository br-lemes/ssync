<?php
declare(strict_types=1);

namespace SSync\Commands;

use SSync\Traits\ConfigTrait;
use SSync\Traits\HasExecutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CatCommand extends BaseCommand
{
    use ConfigTrait;
    use HasExecutable;

    private string $description = 'TODO: cat description';

    public function complete(
        CompletionInput $input,
        CompletionSuggestions $suggestions,
    ): void {
        if ($input->mustSuggestArgumentValuesFor('config')) {
            $suggestions->suggestValues(
                array_map(
                    fn($file): string => basename(dirname($file)),
                    glob(__DIR__ . '/../../config/*/config.php'),
                ),
            );
        }
        if ($input->mustSuggestArgumentValuesFor('file')) {
            $config = $this->getConfig($input->getArgument('config'));
            $currentValue = $input->getCompletionValue();
            $prefix = "{$config['configDir']}/diffs/";
            $files = glob("$prefix$currentValue*");
            foreach ($files as $file) {
                $path = substr($file, strlen($prefix));
                if (is_dir($file)) {
                    $suggestions->suggestValue("$path/");
                } else {
                    $suggestions->suggestValue($path);
                }
            }
        }
    }

    protected function configure(): void
    {
        $this->setName('cat')
            ->setDescription($this->description)
            ->addArgument(
                'config',
                InputArgument::REQUIRED,
                'TODO: config description',
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'TODO: file description',
            );
    }

    function exec(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getConfig($input->getArgument('config'));
        $file = $input->getArgument('file');
        $diffFile = "{$config['configDir']}/diffs/$file";
        if (!file_exists($diffFile)) {
            $this->error('Diff not found.');
            return Command::FAILURE;
        }
        if (!posix_isatty(STDOUT) || !$this->hasExecutable('bat')) {
            $output->write(file_get_contents($diffFile));
            return Command::SUCCESS;
        }
        $command = ['bat', $diffFile];
        $process = new Process($command);
        if ($process->isTtySupported()) {
            $process->setTty(true);
        } else {
            $process->setPty(true);
        }
        $process->run();
        $output->write($process->getOutput());
        return $process->getExitCode();
    }
}
