<?php
declare(strict_types=1);

namespace SSync\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SSync\Traits\ConfigTrait;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class SyncCommand extends BaseCommand
{
    use ConfigTrait;

    private string $description = 'TODO: sync description';

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
        $this->setName('sync')
            ->setDescription($this->description)
            ->addArgument(
                'config',
                InputArgument::REQUIRED,
                'TODO: config description',
            )
            ->addArgument(
                'external',
                InputArgument::IS_ARRAY,
                'TODO: external description',
            );
    }

    function exec(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getConfig($input->getArgument('config'));
        $external = $input->getArgument('external');
        $diffsDir = "{$config['configDir']}/diffs";
        $unisonDir = "{$config['configDir']}/unison";
        $include = "$unisonDir/include";
        $profile = "$unisonDir/default.prf";
        $fs = new Filesystem();
        $fs->mkdir($diffsDir, 0755);
        $fs->mkdir($unisonDir, 0755);
        if (!file_exists($profile)) {
            file_put_contents(
                $profile,
                <<<EOF
                root   = {$config['root1']}
                root   = {$config['root2']}
                include include

                EOF
                ,
            );
        }

        $diffFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $diffsDir,
                RecursiveDirectoryIterator::SKIP_DOTS,
            ),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );
        file_put_contents($include, '');
        foreach ($diffFiles as $file) {
            $path = substr($file->getPathname(), strlen($diffsDir) + 1);
            $a = "{$config['root1']}/$path";
            $b = "{$config['root2']}/$path";
            $diff = file_get_contents($file->getPathname());
            $command = ['diff', '-u', '--label', $a, '--label', $b, $a, $b];
            $process = new Process($command);
            $process->run();
            if ($diff !== $process->getOutput()) {
                $output->writeln("Diff changed for: <info>$path</info>");
                continue;
            }
            file_put_contents($include, "ignore = Path $path\n", FILE_APPEND);
        }

        $command = array_merge(['unison-gui', 'default'], $external);
        $process = new Process($command);
        $process->setEnv(['UNISON' => $unisonDir]);
        $process->setTty(true);
        $process->setTimeout(null);
        $process->run();
        return $process->getExitCode();
    }
}
