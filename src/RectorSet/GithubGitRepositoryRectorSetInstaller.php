<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\DateTime\DateTimeProvider;
use Rector\RectorCI\Entity\GithubGitRepository;
use Rector\RectorCI\Entity\RectorSet;
use Rector\RectorCI\Entity\GithubGitRepositoryRectorSet;
use Rector\RectorCI\RectorSet\Exception\RectorSetAlreadyInstalledException;

final class GithubGitRepositoryRectorSetInstaller
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DateTimeProvider
     */
    private $dateTimeProvider;

    public function __construct(
        EntityManagerInterface $entityManager,
        DateTimeProvider $dateTimeProvider
    ) {
        $this->entityManager = $entityManager;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @throws RectorSetAlreadyInstalledException
     */
    public function install(RectorSet $rectorSet, GithubGitRepository $githubGitRepository): void
    {
        // @TODO it might be already installed
        // throw new RectorSetAlreadyInstalledException();

        // Tady je potreba nejdrive poslat PR a az potom se vytvori pending status

        $repositoryRectorSet = new GithubGitRepositoryRectorSet($githubGitRepository, $rectorSet, $this->dateTimeProvider->provideNow());

        $this->entityManager->persist($repositoryRectorSet);
        $this->entityManager->flush();
    }
}
