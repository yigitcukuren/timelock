<?php

namespace TimeLock;

class VCSFactory
{
    /**
     * Creates an instance of the appropriate VCS implementation based on the provided type.
     *
     * @param string $vcsType   The type of VCS (e.g., 'git').
     * @param string $directory The directory path of the VCS repository.
     *
     * @return VCSInterface              An instance of a VCS implementation.
     * @throws \InvalidArgumentException If the VCS type is unsupported.
     */
    public function create(string $vcsType, string $directory): VCSInterface
    {
        switch (strtolower($vcsType)) {
            case 'git':
                return new GitVCS($directory);
            default:
                throw new \InvalidArgumentException("Unsupported VCS type: $vcsType");
        }
    }
}
