<?php
declare(strict_types=1);

namespace SSync\Commands;

use SSync\Traits\ConfigTrait;
use SSync\Traits\ConstTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class AddCommand extends BaseCommand
{
    use ConfigTrait;
    use ConstTrait;

    private string $description = 'Adds a file to the diff store by creating a diff.';

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
        $this->setName('add')
            ->setDescription($this->description)
            ->addArgument('config', InputArgument::REQUIRED, self::CONFIG_NAME)
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The path to the file to add.',
            );
    }

    function exec(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getConfig($input->getArgument('config'));
        $file = $input->getArgument('file');
        $a = "{$config['root1']}/$file";
        $b = "{$config['root2']}/$file";

        $command = ['diff', '-u', '--label', $a, '--label', $b, $a, $b];
        $process = new Process($command);
        $process->run();
        $diff = $process->getOutput();
        if (!$diff) {
            $this->error('No changes.');
        }

        $diffFile = "{$config['configDir']}/diffs/$file";
        $diffDir = dirname($diffFile);
        $fs = new Filesystem();
        $fs->mkdir($diffDir, 0755);
        file_put_contents($diffFile, $diff);
        $output->writeln("Saved diff to: <info>$diffFile</info>");
        return Command::SUCCESS;
    }
}
