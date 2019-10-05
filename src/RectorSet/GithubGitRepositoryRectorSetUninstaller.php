<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\Entity\GithubGitRepository;
use Rector\RectorCI\Entity\RectorSet;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotActiveException;
use Rector\RectorCI\Repository\GithubGitRepositoryRectorSetRepository;

final class GithubGitRepositoryRectorSetUninstaller
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GithubGitRepositoryRectorSetRepository
     */
    private $rectorSetActivationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GithubGitRepositoryRectorSetRepository $rectorSetActivationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->rectorSetActivationRepository = $rectorSetActivationRepository;
    }

    /**
     * @throws RectorSetNotActiveException
     */
    public function uninstall(RectorSet $rectorSet, GithubGitRepository $githubGitRepository): void
    {
        $repositoryRectorSet = $this->rectorSetActivationRepository->getRectorSetActivationForRepository(
            $rectorSet->getId(),
            $githubGitRepository->getId()
        );

        $this->entityManager->remove($repositoryRectorSet);
        $this->entityManager->flush();
    }
}
