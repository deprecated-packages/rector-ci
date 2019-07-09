<?php declare(strict_types=1);

namespace Rector\RectorCI\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\Entity\RectorSet;

final class RectorSetRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @return RectorSet[]
     */
    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->from(RectorSet::class, 'rector_set')
            ->select('rector_set')
            ->getQuery()
            ->getResult();
    }
}
