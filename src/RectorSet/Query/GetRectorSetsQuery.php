<?php declare(strict_types=1);

namespace Rector\RectorCI\RectorSet\Query;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\Entity\RectorSet;

final class GetRectorSetsQuery
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
    public function query(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->from(RectorSet::class, 'rector_set')
            ->select('rector_set')
            ->orderBy('rector_set.title')
            ->getQuery()
            ->getResult();
    }
}
