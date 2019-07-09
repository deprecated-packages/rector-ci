<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Rector\RectorCI\Entity\GithubGitRepository;
use Rector\RectorCI\Entity\RectorSet;
use Rector\RectorCI\Repository\RectorSetActivationRepository;

final class RectorSetActivationChecker
{
    /**
     * @var RectorSetActivationRepository
     */
    private $rectorSetActivationRepository;

    public function __construct(RectorSetActivationRepository $rectorSetActivationRepository)
    {
        $this->rectorSetActivationRepository = $rectorSetActivationRepository;
    }

    public function isSetActiveForRepository(RectorSet $rectorSet, GithubGitRepository $githubGitRepository): bool
    {
        return $this->rectorSetActivationRepository->doesActivationExist(
            $githubGitRepository->getId(),
            $rectorSet->getId()
        );
    }
}
