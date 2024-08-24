<?php

namespace TimeLock\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TimeLock\Command\CheckCommand;
use TimeLock\GitVCS;
use TimeLock\VCSFactory;

/**
 * Class CheckCommandTest
 *
 * Tests the CheckCommand functionality.
 */
class CheckCommandTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * Sets up the test environment by initializing the application
     * and CommandTester with a mocked VCSFactory.
     */
    protected function setUp(): void
    {
        $this->application = new Application();

        // Create a mock of VCSFactory
        $mockFactory = $this->createMock(VCSFactory::class);

        // Mock the create method to return a mocked GitVCS object
        $mockVCS = $this->createMock(GitVCS::class);
        $mockVCS->method('getFiles')->willReturn(['file1.txt', 'file2.txt']);
        $mockVCS->method('getFileInfo')->willReturn([
            'file1.txt' => ['author' => 'John Doe', 'timestamp' => strtotime('-6 years'), 'changes' => 1],
            'file2.txt' => ['author' => 'Jane Doe', 'timestamp' => strtotime('-4 years'), 'changes' => 3],
        ]);

        $mockFactory->method('create')->willReturn($mockVCS);

        // Initialize the CheckCommand with the mocked factory
        $command = new CheckCommand($mockFactory);
        $this->application->add($command);

        // Prepare CommandTester for the CheckCommand
        $command = $this->application->find('check');
        $this->commandTester = new CommandTester($command);
    }

    /**
     * Tests the CheckCommand with default settings.
     */
    public function testCheckCommandWithDefaultSettings(): void
    {
        $this->commandTester->execute([
            'path' => __DIR__,
            '--config' => __DIR__ . '/../fixtures/timelock.yml',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('file1.txt', $output);
        $this->assertStringNotContainsString('file2.txt', $output);
        $this->assertStringContainsString('Execution time:', $output);
    }

    /**
     * Tests the CheckCommand with JSON output format.
     */
    public function testCheckCommandWithJsonOutput(): void
    {
        $this->commandTester->execute([
            'path' => __DIR__,
            '--config' => __DIR__ . '/../fixtures/timelock.yml',
            '--output-format' => 'json',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        $this->assertStringContainsString('file1.txt', $output);
        $this->assertStringNotContainsString('file2.txt', $output);
    }

    /**
     * Tests that the CheckCommand fails when no configuration file is provided.
     */
    public function testCheckCommandFailsWithoutConfig(): void
    {
        $this->commandTester->execute([
            'path' => __DIR__,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Configuration file \'timelock.yml\' not found.', $output);
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}
