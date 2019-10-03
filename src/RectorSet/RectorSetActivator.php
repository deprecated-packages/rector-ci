<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\DateTime\DateTimeProvider;
use Rector\RectorCI\Entity\GithubGitRepository;
use Rector\RectorCI\Entity\RectorSet;
use Rector\RectorCI\Entity\RectorSetActivation;
use Rector\RectorCI\RectorSet\Exception\RectorSetAlreadyActivatedException;

final class RectorSetActivator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RectorSetActivationChecker
     */
    private $rectorSetActivationChecker;

    /**
     * @var DateTimeProvider
     */
    private $dateTimeProvider;

    public function __construct(
        EntityManagerInterface $entityManager,
        RectorSetActivationChecker $rectorSetActivationChecker,
        DateTimeProvider $dateTimeProvider
    ) {
        $this->entityManager = $entityManager;
        $this->rectorSetActivationChecker = $rectorSetActivationChecker;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @throws RectorSetAlreadyActivatedException
     */
    public function activateSetForRepository(RectorSet $rectorSet, GithubGitRepository $githubGitRepository): void
    {
        if ($this->rectorSetActivationChecker->isSetActiveForRepository($rectorSet, $githubGitRepository)) {
            throw new RectorSetAlreadyActivatedException();
        }

        $activation = new RectorSetActivation($githubGitRepository, $rectorSet, $this->dateTimeProvider->provideNow());

        $this->entityManager->persist($activation);
        $this->entityManager->flush();
    }
}
