<?php declare(strict_types=1);

namespace Rector\RectorCI\Github;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\Entity\GithubRepository;
use Rector\RectorCI\Github\Exceptions\GithubRepositoryNotFoundException;
use Rector\RectorCI\Github\Query\GetGithubRepositoryQuery;

final class GithubRepositoryGetter
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GetGithubRepositoryQuery
     */
    private $getGithubRepositoryByQuery;


    public function __construct(EntityManagerInterface $entityManager, GetGithubRepositoryQuery $getGithubRepositoryByQuery)
    {
        $this->entityManager = $entityManager;
        $this->getGithubRepositoryByQuery = $getGithubRepositoryByQuery;
    }


    public function getGithubRepositoryOrCreateItIfNotExists(int $id): GithubRepository
    {
        try {
            $repository = $this->getGithubRepositoryByQuery->query($id);
        } catch (GithubRepositoryNotFoundException $exception) {
            $repository = new GithubRepository($id);

            $this->entityManager->persist($repository);
            $this->entityManager->flush();;
        }

        return $repository;
    }
}
