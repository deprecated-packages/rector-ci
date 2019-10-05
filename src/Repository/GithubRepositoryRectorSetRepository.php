<?php declare(strict_types=1);

namespace Rector\RectorCI\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Ramsey\Uuid\UuidInterface;
use Rector\RectorCI\Entity\GithubRepositoryRectorSetInstallation;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotActiveException;

final class GithubRepositoryRectorSetRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function doesActivationExist(UuidInterface $githubRepositoryId, UuidInterface $rectorSetId): bool
    {
        return (bool) $this->entityManager->createQueryBuilder()
            ->from(GithubRepositoryRectorSetInstallation::class, 'rectorSetInstallation')
            ->select('COUNT(rectorSetInstallation.rectorSet)')
            ->where('rectorSetInstallation.githubRepository = :githubRepository')
            ->andWhere('rectorSetInstallation.rectorSet = :rectorSet')
            ->setParameter('githubRepository', $githubRepositoryId)
            ->setParameter('rectorSet', $rectorSetId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws RectorSetNotActiveException
     */
    public function getRectorSetActivationForRepository(
        UuidInterface $rectorSetId,
        UuidInterface $githubRepositoryId
    ): GithubRepositoryRectorSetInstallation {
        try {
            return $this->entityManager->createQueryBuilder()
                ->from(GithubRepositoryRectorSetInstallation::class, 'rectorSetInstallation')
                ->select('rectorSetInstallation')
                ->where('rectorSetInstallation.githubRepository = :githubRepository')
                ->andWhere('rectorSetInstallation.rectorSet = :rectorSet')
                ->setParameter('githubRepository', $githubRepositoryId)
                ->setParameter('rectorSet', $rectorSetId)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $noResultException) {
            throw new RectorSetNotActiveException();
        }
    }
}
