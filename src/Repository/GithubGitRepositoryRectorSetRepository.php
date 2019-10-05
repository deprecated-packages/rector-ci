<?php declare(strict_types=1);

namespace Rector\RectorCI\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Ramsey\Uuid\UuidInterface;
use Rector\RectorCI\Entity\GithubGitRepositoryRectorSet;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotActiveException;

final class GithubGitRepositoryRectorSetRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function doesActivationExist(UuidInterface $githubGitRepositoryId, UuidInterface $rectorSetId): bool
    {
        return (bool) $this->entityManager->createQueryBuilder()
            ->from(GithubGitRepositoryRectorSet::class, 'rectorSetInstallation')
            ->select('COUNT(rectorSetInstallation.rectorSet)')
            ->where('rectorSetInstallation.githubGitRepository = :githubGitRepository')
            ->andWhere('rectorSetInstallation.rectorSet = :rectorSet')
            ->setParameter('githubGitRepository', $githubGitRepositoryId)
            ->setParameter('rectorSet', $rectorSetId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws RectorSetNotActiveException
     */
    public function getRectorSetActivationForRepository(
        UuidInterface $rectorSetId,
        UuidInterface $githubGitRepositoryId
    ): GithubGitRepositoryRectorSet {
        try {
            return $this->entityManager->createQueryBuilder()
                ->from(GithubGitRepositoryRectorSet::class, 'rectorSetInstallation')
                ->select('rectorSetInstallation')
                ->where('rectorSetInstallation.githubGitRepository = :githubGitRepository')
                ->andWhere('rectorSetInstallation.rectorSet = :rectorSet')
                ->setParameter('githubGitRepository', $githubGitRepositoryId)
                ->setParameter('rectorSet', $rectorSetId)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $noResultException) {
            throw new RectorSetNotActiveException();
        }
    }
}
