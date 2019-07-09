<?php declare(strict_types=1);

namespace Rector\RectorCI\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;
use Rector\RectorCI\Entity\RectorSetActivation;

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
}
