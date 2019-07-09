<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Rector\RectorCI\Entity\GithubGitRepository;

final class RectorSetActivationChecker
{
    public function isSetActiveForRepository(GithubGitRepository $gitRepository): bool
    {
        return rand(0, 1) === 1;
    }
}
