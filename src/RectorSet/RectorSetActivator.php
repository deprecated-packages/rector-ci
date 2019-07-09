<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Rector\RectorCI\Entity\GithubGitRepository;
use Rector\RectorCI\Entity\RectorSet;

final class RectorSetActivator
{
    public function activateForRepository(GithubGitRepository $gitRepository, RectorSet $rectorSet): void
    {
        // TODO: for which repository?
    }

    public function deactivateForRepository(GithubGitRepository $gitRepository, RectorSet $rectorSet): void
    {
        // TODO: for which repository?
    }
}
