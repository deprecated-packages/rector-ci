<?php declare(strict_types=1);

namespace Rector\RectorCI\Github\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Rector\RectorCI\Entity\GithubRepository;
use Rector\RectorCI\Github\Exceptions\GithubRepositoryNotFoundException;

final class GetGithubRepositoryQuery
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function query(int $id): GithubRepository
    {
        try {
            return $this->entityManager->createQueryBuilder()
                ->from(GithubRepository::class, 'repository')
                ->select('repository')
                ->where('repository.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();
        }
        catch (NoResultException $noResultException) {
            throw new GithubRepositoryNotFoundException();
        }
    }
}
