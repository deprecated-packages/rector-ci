<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\DateTime\DateTimeProvider;
use Rector\RectorCI\Entity\GithubRepositoryRectorSetInstallation;
use Rector\RectorCI\Github\Query\GetGithubRepositoryQuery;
use Rector\RectorCI\RectorSet\Exception\RectorSetAlreadyInstalledException;
use Rector\RectorCI\RectorSet\Query\GetRectorSetByNameQuery;

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

    /**
     * @var GetRectorSetByNameQuery
     */
    private $getRectorSetByNameQuery;

    /**
     * @var GetGithubRepositoryQuery
     */
    private $getGithubRepositoryQuery;


    public function __construct(
        EntityManagerInterface $entityManager,
        DateTimeProvider $dateTimeProvider,
        GetRectorSetByNameQuery $getRectorSetByNameQuery,
        GetGithubRepositoryQuery $getGithubRepositoryQuery
    ) {
        $this->entityManager = $entityManager;
        $this->dateTimeProvider = $dateTimeProvider;
        $this->getRectorSetByNameQuery = $getRectorSetByNameQuery;
        $this->getGithubRepositoryQuery = $getGithubRepositoryQuery;
    }

    /**
     * @throws RectorSetAlreadyInstalledException
     */
    public function install(string $rectorSetName, int $githubRepositoryId): void
    {
        $rectorSet = $this->getRectorSetByNameQuery->query($rectorSetName);
        $githubRepository = $this->getGithubRepositoryQuery->query($githubRepositoryId);


        // @TODO it might be already installed
        // throw new RectorSetAlreadyInstalledException();

        // Tady je potreba nejdrive poslat PR a az potom se vytvori pending status

        $repositoryRectorSet = new GithubRepositoryRectorSetInstallation($githubRepository, $rectorSet, $this->dateTimeProvider->provideNow());

        $this->entityManager->persist($repositoryRectorSet);
        $this->entityManager->flush();
    }
}
