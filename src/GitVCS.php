<?php

namespace TimeLock;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class GitVCS
 *
 * Implements VCSInterface for Git repositories.
 */
class GitVCS implements VCSInterface
{
    /**
     * @var string The directory path for the Git repository.
     */
    private $directory;

    /**
     * GitVCS constructor.
     *
     * @param string $directory The directory path of the Git repository.
     */
    public function __construct(string $directory)
    {
        $this->directory = realpath($directory);
    }

    /**
     * Checks if the directory is a valid Git repository.
     *
     * @return bool True if the directory is a Git repository, false otherwise.
     */
    public function isGitRepository(): bool
    {
        $process = $this->createProcess([
            'git',
            '-C',
            $this->directory,
            'rev-parse',
            '--is-inside-work-tree'
        ]);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Retrieves a list of files tracked by the Git repository.
     *
     * @return array The list of file paths.
     *
     * @throws \RuntimeException      If the directory is not a Git repository.
     * @throws ProcessFailedException If the Git process fails.
     */
    public function getFiles(): array
    {
        if (!$this->isGitRepository()) {
            throw new \RuntimeException(
                "Directory '{$this->directory}' is not a Git repository."
            );
        }

        $process = $this->createProcess([
            'git',
            '-C',
            $this->directory,
            'ls-files'
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return explode("\n", trim($process->getOutput()));
    }

    /**
     * Retrieves information about files in the Git repository,
     * including author, last modification date, and number of changes.
     *
     * @return array An associative array of file information.
     *
     * @throws ProcessFailedException If the Git process fails.
     */
    public function getFileInfo(): array
    {
        $process = $this->createProcess([
            'git',
            '-C',
            $this->directory,
            'log',
            '--pretty=format:%H,%an,%at',
            '--name-only'
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $lines = explode("\n", trim($process->getOutput()));
        $info = [];
        $currentCommit = null;

        foreach ($lines as $line) {
            if (strpos($line, ',') !== false) {
                $parts = explode(',', $line);
                $commit = $this->sanitizeString($parts[0] ?? '');
                $author = $this->sanitizeString($parts[1] ?? '');
                $timestamp = $parts[2] ?? strtotime('50 years ago');
                $currentCommit = compact('author', 'timestamp');
            } elseif ($line) {
                $sanitizedFile = $this->sanitizeString($line);
                if (!isset($info[$sanitizedFile])) {
                    $info[$sanitizedFile] = $currentCommit + ['changes' => 0];
                }
                $info[$sanitizedFile]['changes']++;
            }
        }

        return $info;
    }

    /**
     * Creates a new process instance for a given command.
     *
     * @param array $command The command to be executed.
     *
     * @return Process The process instance.
     */
    protected function createProcess(array $command): Process
    {
        return new Process($command);
    }

    /**
     * Sanitizes a string by removing control characters.
     *
     * @param string $string The string to sanitize.
     *
     * @return string The sanitized string.
     */
    private function sanitizeString(string $string): string
    {
        return preg_replace('/[\x00-\x1F\x7F]/u', '', $string);
    }
}
