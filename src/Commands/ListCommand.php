<?php
declare(strict_types=1);

namespace SSync\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SSync\Traits\ConfigTrait;
use SSync\Traits\ConstTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends BaseCommand
{
    use ConfigTrait;
    use ConstTrait;

    private string $description = 'Lists the files in the diff store.';

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
        $this->setName('ls')
            ->setDescription($this->description)
            ->addArgument('config', InputArgument::REQUIRED, self::CONFIG_NAME);
    }

    function exec(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getConfig($input->getArgument('config'));
        $dir = "{$config['configDir']}/diffs";
        if (!is_dir($dir)) {
            $this->error("Directory $dir does not exist");
            return Command::FAILURE;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                RecursiveDirectoryIterator::SKIP_DOTS,
            ),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );
        foreach ($files as $file) {
            $output->writeln(substr($file->getPathname(), strlen($dir) + 1));
        }
        return Command::SUCCESS;
    }
}
