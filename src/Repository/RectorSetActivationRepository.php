<?php declare(strict_types=1);

namespace Rector\RectorCI\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Ramsey\Uuid\UuidInterface;
use Rector\RectorCI\Entity\RectorSetActivation;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotActiveException;

final class RectorSetActivationRepository
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
            ->from(RectorSetActivation::class, 'activation')
            ->select('COUNT(activation.rectorSet)')
            ->where('activation.githubGitRepository = :githubGitRepository')
            ->andWhere('activation.rectorSet = :rectorSet')
            ->setParameter('githubGitRepository', $githubGitRepositoryId)
            ->setParameter('rectorSet', $rectorSetId)
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @throws RectorSetNotActiveException
     */
    public function getRectorSetActivationForRepository(UuidInterface $rectorSetId, UuidInterface $githubGitRepositoryId): RectorSetActivation
    {
        try {
            return $this->entityManager->createQueryBuilder()
                ->from(RectorSetActivation::class, 'activation')
                ->select('activation')
                ->where('activation.githubGitRepository = :githubGitRepository')
                ->andWhere('activation.rectorSet = :rectorSet')
                ->setParameter('githubGitRepository', $githubGitRepositoryId)
                ->setParameter('rectorSet', $rectorSetId)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $exception) {
            throw new RectorSetNotActiveException();
        }
    }
}
