<?php

namespace TimeLock;

/**
 * Interface VCSInterface
 *
 * Defines the contract for Version Control System (VCS) implementations.
 */
interface VCSInterface
{
    /**
     * Retrieves a list of files tracked by the VCS.
     *
     * @return array An array of file paths.
     */
    public function getFiles(): array;

    /**
     * Retrieves information about files in the VCS, including author and last modification date.
     *
     * @return array An associative array containing file information.
     */
    public function getFileInfo(): array;
}
