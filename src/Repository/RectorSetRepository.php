<?php declare(strict_types=1);

namespace Rector\RectorCI\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Rector\RectorCI\Entity\RectorSet;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotFoundException;

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
            ->orderBy('rector_set.title')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws RectorSetNotFoundException
     */
    public function getByName(string $name): RectorSet
    {
        try {
            return $this->entityManager->createQueryBuilder()
                ->from(RectorSet::class, 'rector_set')
                ->select('rector_set')
                ->andWhere('rector_set.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $noResultException) {
            throw new RectorSetNotFoundException();
        }
    }
}
