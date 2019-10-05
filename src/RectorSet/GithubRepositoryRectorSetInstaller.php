<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\DateTime\DateTimeProvider;
use Rector\RectorCI\Entity\GithubRepository;
use Rector\RectorCI\Entity\RectorSet;
use Rector\RectorCI\Entity\GithubRepositoryRectorSetInstallation;
use Rector\RectorCI\RectorSet\Exception\RectorSetAlreadyInstalledException;

final class GithubRepositoryRectorSetInstaller
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
    public function install(RectorSet $rectorSet, GithubRepository $githubRepository): void
    {
        // @TODO it might be already installed
        // throw new RectorSetAlreadyInstalledException();

        // Tady je potreba nejdrive poslat PR a az potom se vytvori pending status

        $repositoryRectorSet = new GithubRepositoryRectorSetInstallation($githubRepository, $rectorSet, $this->dateTimeProvider->provideNow());

        $this->entityManager->persist($repositoryRectorSet);
        $this->entityManager->flush();
    }
}
