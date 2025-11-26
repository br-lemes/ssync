<?php
declare(strict_types=1);

namespace SSync\Commands;

use SSync\Traits\ConfigTrait;
use SSync\Traits\HasExecutable;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DiffCommand extends BaseCommand
{
    use ConfigTrait;
    use HasExecutable;

    private string $description = 'TODO: diff description';

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
            $files = glob("{$config['root1']}/$currentValue*");
            foreach ($files as $file) {
                $path = substr($file, strlen($config['root1']) + 1);
                $root2 = "{$config['root2']}/$path";
                if (!file_exists($root2)) {
                    continue;
                }
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
        $this->setName('diff')
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
            )
            ->addOption(
                'inline',
                'i',
                InputOption::VALUE_NONE,
                'Inline display mode for difft output',
            );
    }

    function exec(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getConfig($input->getArgument('config'));
        $file = $input->getArgument('file');
        if (posix_isatty(STDOUT) && $this->hasExecutable('difft')) {
            $command = ['difft'];
            if ($input->getOption('inline')) {
                $command[] = '--display';
                $command[] = 'inline';
            }
        } else {
            $command = ['diff', '-u'];
        }
        $command[] = "{$config['root1']}/$file";
        $command[] = "{$config['root2']}/$file";
        $process = new Process($command);
        if ($process->isTtySupported()) {
            $process->setTty(true);
        }
        $process->run();
        $output->write($process->getOutput());
        return $process->getExitCode();
    }
}
