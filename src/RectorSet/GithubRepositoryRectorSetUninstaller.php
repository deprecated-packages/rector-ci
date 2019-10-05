<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\Entity\GithubRepository;
use Rector\RectorCI\Entity\RectorSet;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotActiveException;
use Rector\RectorCI\Repository\GithubRepositoryRectorSetRepository;

final class GithubRepositoryRectorSetUninstaller
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GithubRepositoryRectorSetRepository
     */
    private $rectorSetActivationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GithubRepositoryRectorSetRepository $rectorSetActivationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->rectorSetActivationRepository = $rectorSetActivationRepository;
    }

    /**
     * @throws RectorSetNotActiveException
     */
    public function uninstall(RectorSet $rectorSet, GithubRepository $githubRepository): void
    {
        $repositoryRectorSet = $this->rectorSetActivationRepository->getRectorSetActivationForRepository(
            $rectorSet->getId(),
            $githubRepository->getId()
        );

        $this->entityManager->remove($repositoryRectorSet);
        $this->entityManager->flush();
    }
}
