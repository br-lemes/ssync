<?php
declare(strict_types=1);

namespace SSync\Commands;

use SSync\Traits\ConfigTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class RemoveCommand extends BaseCommand
{
    use ConfigTrait;

    private string $description = 'TODO: rm description';

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
        $this->setName('rm')
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
        $fs = new Filesystem();
        $fs->remove($diffFile);
        $output->writeln("Removed diff: <info>$diffFile</info>");
        return Command::SUCCESS;
    }
}
