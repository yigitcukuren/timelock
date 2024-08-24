<?php

namespace TimeLock\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use TimeLock\GitVCS;

/**
 * Class GitVCSTest
 *
 * Unit tests for the GitVCS class, simulating Git operations.
 */
class GitVCSTest extends TestCase
{
    /**
     * @var GitVCS The GitVCS instance under test.
     */
    private $gitVCS;

    /**
     * Sets up the test environment by initializing the GitVCS instance.
     */
    protected function setUp(): void
    {
        $this->gitVCS = $this->getMockBuilder(GitVCS::class)
            ->setConstructorArgs([__DIR__])
            ->onlyMethods(['createProcess'])
            ->getMock();
    }

    /**
     * Tests that the isGitRepository method correctly identifies a Git repository.
     */
    public function testIsGitRepository(): void
    {
        $process = $this->createMock(Process::class);
        $process->method('run')->willReturn(0); // Simulate successful run
        $process->method('isSuccessful')->willReturn(true);

        $this->gitVCS->expects($this->once())
            ->method('createProcess')
            ->willReturn($process);

        $this->assertTrue($this->gitVCS->isGitRepository());
    }

    /**
     * Tests that the getFiles method throws a RuntimeException for a non-Git repository.
     */
    public function testGetFilesThrowsExceptionForNonGitRepo(): void
    {
        $process = $this->createMock(Process::class);
        $process->method('run')->willReturn(1); // Simulate failed run
        $process->method('isSuccessful')->willReturn(false);

        $this->gitVCS->expects($this->once())
            ->method('createProcess')
            ->willReturn($process);

        $this->expectException(\RuntimeException::class);
        $this->gitVCS->getFiles();
    }

    /**
     * Tests that the getFiles method correctly returns a list of files in the repository.
     */
    public function testGetFiles(): void
    {
        $process = $this->createMock(Process::class);
        $process->method('run')->willReturn(0); // Simulate successful run
        $process->method('isSuccessful')->willReturn(true);
        $process->method('getOutput')->willReturn("file1.txt\nfile2.txt");

        // Ensure that createProcess is called twice: once for isGitRepository and once for getFiles
        $this->gitVCS->expects($this->exactly(2))
            ->method('createProcess')
            ->willReturn($process);

        $files = $this->gitVCS->getFiles();

        // Add assertions to check the returned files
        $this->assertIsArray($files);
        $this->assertCount(2, $files);
        $this->assertEquals('file1.txt', $files[0]);
        $this->assertEquals('file2.txt', $files[1]);
    }

    /**
     * Tests that the getFileInfo method returns correct information about files in the repository.
     */
    public function testGetFileInfo(): void
    {
        $process = $this->createMock(Process::class);
        $process->method('run')->willReturn(0); // Simulate successful run
        $process->method('isSuccessful')->willReturn(true);
        $process->method('getOutput')->willReturn("hash1,John Doe,1609459200\nfile1.txt\nfile2.txt");

        $this->gitVCS->expects($this->once())
            ->method('createProcess')
            ->willReturn($process);

        $fileInfo = $this->gitVCS->getFileInfo();
        $this->assertIsArray($fileInfo);
        $this->assertArrayHasKey('file1.txt', $fileInfo);
        $this->assertArrayHasKey('file2.txt', $fileInfo);
        $this->assertEquals('John Doe', $fileInfo['file1.txt']['author']);
    }
}
