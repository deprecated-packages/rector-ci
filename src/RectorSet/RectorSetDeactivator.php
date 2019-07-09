<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\Entity\GithubGitRepository;
use Rector\RectorCI\Entity\RectorSet;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotActiveException;
use Rector\RectorCI\Repository\RectorSetActivationRepository;

final class RectorSetDeactivator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RectorSetActivationRepository
     */
    private $rectorSetActivationRepository;


    public function __construct(
        EntityManagerInterface $entityManager,
        RectorSetActivationRepository $rectorSetActivationRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->rectorSetActivationRepository = $rectorSetActivationRepository;
    }


    /**
     * @throws RectorSetNotActiveException
     */
    public function deactivateSetForRepository(RectorSet $rectorSet, GithubGitRepository $gitRepository): void
    {
        $activation = $this->rectorSetActivationRepository->getRectorSetActivationForRepository(
            $rectorSet->getId(),
            $gitRepository->getId()
        );

        $this->entityManager->remove($activation);
        $this->entityManager->flush();
    }
}
