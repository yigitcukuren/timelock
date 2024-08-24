<?php

namespace TimeLock\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TimeLock\VCSFactory;
use TimeLock\VCSInterface;

/**
 * Class CheckCommand
 *
 * This command checks for files that have remained unchanged since a specific date.
 */
class CheckCommand extends Command
{
    /**
     * @var VCSFactory The VCS factory instance.
     */
    private $vcsFactory;

    /**
     * CheckCommand constructor.
     *
     * @param VCSFactory $vcsFactory The VCS factory instance
     */
    public function __construct(VCSFactory $vcsFactory)
    {
        $this->vcsFactory = $vcsFactory;
        parent::__construct();
    }

    /**
     * Configures the command options and arguments.
     */
    protected function configure(): void
    {
        $this
            ->setName('check')
            ->setDescription('Checks for files unchanged since a specified date.')
            ->setHelp('This command allows you to check for files that haven\'t been changed since a specific date...')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'The directory path to check',
                '.'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Path to the configuration file'
            )
            ->addOption(
                'output-format',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Output format (table or json)',
                'table'
            );
    }

    /**
     * Executes the command logic.
     *
     * @param InputInterface  $input  The input interface
     * @param OutputInterface $output The output interface
     *
     * @return int Command exit status
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);

        $directory = realpath($input->getArgument('path'));
        $configFile = $input->getOption('config');

        if (empty($configFile) || !file_exists($configFile)) {
            $output->writeln('<error>Configuration file \'timelock.yml\' not found.</error>');
            return Command::FAILURE;
        }

        $config = Yaml::parseFile($configFile);
        $vcsType = $config['vcs'] ?? 'git';
        $outputFormat = $input->getOption('output-format');
        $excludeRegex = $config['excludeRegex'] ?? [];

        try {
            /** @var VCSInterface $vcs */
            $vcs = $this->vcsFactory->create($vcsType, $directory);
        } catch (\InvalidArgumentException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        try {
            $files = $vcs->getFiles();
            $fileInfo = $vcs->getFileInfo();
        } catch (\RuntimeException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        $since = strtotime($config['since'] ?? '5 years ago');
        $excludeAuthors = $config['excludeAuthors'] ?? [];
        $excludePaths = $config['exclude'] ?? [];

        $unchangedFiles = $this->processFiles($files, $fileInfo, $since, $excludeAuthors, $excludePaths, $excludeRegex);

        if (empty($unchangedFiles)) {
            $output->writeln('<info>No unchanged files found.</info>');
        } else {
            if ($outputFormat === 'json') {
                $output->writeln(json_encode($unchangedFiles, JSON_PRETTY_PRINT));
            } else {
                $table = new Table($output);
                $table->setHeaders(['File', 'Author', 'Last Modified', 'Changes']);

                foreach ($unchangedFiles as $fileData) {
                    $table->addRow([
                        $fileData['file'],
                        $fileData['author'],
                        $fileData['last_modified'],
                        $fileData['changes']
                    ]);
                }

                $table->render();
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        if ($outputFormat !== 'json') {
            $output->writeln(sprintf('<info>Execution time: %.2f seconds</info>', $executionTime));
        }

        return Command::SUCCESS;
    }

    /**
     * Processes files to find those that have remained unchanged since the specified date.
     *
     * @param array $files          List of files to process
     * @param array $fileInfo       Information about each file
     * @param int   $since          Timestamp to compare last modified dates against
     * @param array $excludeAuthors Authors to exclude from the check
     * @param array $excludePaths   Paths to exclude from the check
     * @param array $excludeRegex   Regex patterns to exclude from the check
     *
     * @return array List of unchanged files with details
     */
    private function processFiles(
        array $files,
        array $fileInfo,
        int $since,
        array $excludeAuthors,
        array $excludePaths,
        array $excludeRegex
    ): array {
        $unchangedFiles = [];

        foreach ($files as $file) {
            if (!isset($fileInfo[$file])) {
                continue;
            }

            $author = $fileInfo[$file]['author'] ?? null;
            $lastModified = $fileInfo[$file]['timestamp'] ?? null;
            $changes = $fileInfo[$file]['changes'] ?? 0;

            if ($lastModified > $since) {
                continue;
            }

            if (in_array($author, $excludeAuthors)) {
                continue;
            }

            foreach ($excludePaths as $path) {
                if (strpos($file, $path) !== false) {
                    continue 2; // skip to next file
                }
            }

            foreach ($excludeRegex as $pattern) {
                if (preg_match($pattern, $file)) {
                    continue 2; // skip to next file
                }
            }

            $unchangedFiles[] = [
                'file' => $file,
                'author' => $author,
                'last_modified' => date('Y-m-d H:i:s', $lastModified),
                'changes' => $changes,
            ];
        }

        return $unchangedFiles;
    }
}
