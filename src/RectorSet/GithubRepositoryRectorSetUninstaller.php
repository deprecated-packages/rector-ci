<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\Github\Query\GetGithubRepositoryQuery;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotInstalledException;
use Rector\RectorCI\RectorSet\Query\GetGithubRepositoryRectorSetInstallationQuery;
use Rector\RectorCI\RectorSet\Query\GetRectorSetByNameQuery;

final class GithubRepositoryRectorSetUninstaller
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GetGithubRepositoryRectorSetInstallationQuery
     */
    private $getGithubRepositoryRectorSetInstallationQuery;


    public function __construct(
        EntityManagerInterface $entityManager,
        GetGithubRepositoryRectorSetInstallationQuery $getGithubRepositoryRectorSetInstallationQuery
    ) {
        $this->entityManager = $entityManager;
        $this->getGithubRepositoryRectorSetInstallationQuery = $getGithubRepositoryRectorSetInstallationQuery;
    }

    /**
     * @throws RectorSetNotInstalledException
     */
    public function uninstall(string $rectorSetName, int $githubRepositoryId): void
    {
        $repositoryRectorSet = $this->getGithubRepositoryRectorSetInstallationQuery->query(
            $rectorSetName,
            $githubRepositoryId
        );

        $this->entityManager->remove($repositoryRectorSet);
        $this->entityManager->flush();
    }
}
