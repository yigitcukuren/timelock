<?php

namespace TimeLock\Tests;

use PHPUnit\Framework\TestCase;
use TimeLock\GitVCS;
use TimeLock\VCSFactory;

/**
 * Class VCSFactoryTest
 *
 * Unit tests for the VCSFactory class, ensuring it creates the correct VCS instances.
 */
class VCSFactoryTest extends TestCase
{
    /**
     * Tests that the factory correctly creates a GitVCS instance.
     */
    public function testCreateGitVCS(): void
    {
        $factory = new VCSFactory();
        $vcs = $factory->create('git', __DIR__);
        $this->assertInstanceOf(GitVCS::class, $vcs);
    }

    /**
     * Tests that the factory throws an InvalidArgumentException for an unsupported VCS type.
     */
    public function testCreateThrowsExceptionForUnsupportedVCS(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = new VCSFactory();
        $factory->create('unsupported_vcs', __DIR__);
    }
}
