<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Rector\RectorCI\Entity\GithubRepositoryRectorSetInstallation;
use Rector\RectorCI\Github\Query\GetGithubRepositoryQuery;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotInstalledException;

final class GetGithubRepositoryRectorSetInstallationQuery
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GetRectorSetByNameQuery
     */
    private $getRectorSetByNameQuery;

    /**
     * @var GetGithubRepositoryQuery
     */
    private $getGithubRepositoryQuery;


    public function __construct(EntityManagerInterface $entityManager,
        GetRectorSetByNameQuery $getRectorSetByNameQuery,
        GetGithubRepositoryQuery $getGithubRepositoryQuery
    )
    {
        $this->entityManager = $entityManager;
        $this->getRectorSetByNameQuery = $getRectorSetByNameQuery;
        $this->getGithubRepositoryQuery = $getGithubRepositoryQuery;
    }


    /**
     * @throws RectorSetNotInstalledException
     */
    public function query(
        string $rectorSetName,
        int $githubRepositoryId
    ): GithubRepositoryRectorSetInstallation {
        $rectorSet = $this->getRectorSetByNameQuery->query($rectorSetName);
        $githubRepository = $this->getGithubRepositoryQuery->query($githubRepositoryId);

        try {
            return $this->entityManager->createQueryBuilder()
                ->from(GithubRepositoryRectorSetInstallation::class, 'rectorSetInstallation')
                ->select('rectorSetInstallation')
                ->where('rectorSetInstallation.githubRepository = :githubRepository')
                ->andWhere('rectorSetInstallation.rectorSet = :rectorSet')
                ->setParameter('githubRepository', $githubRepository->getId())
                ->setParameter('rectorSet', $rectorSet->getId())
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $noResultException) {
            throw new RectorSetNotInstalledException();
        }
    }
}
