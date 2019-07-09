<?php declare(strict_types=1);

namespace Rector\RectorCI\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Rector\RectorCI\Doctrine\IdentityProvider;
use Rector\RectorCI\Entity\GithubGitRepository;

final class GithubGitRepositoryRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var IdentityProvider
     */
    private $identityProvider;

    public function __construct(IdentityProvider $identityProvider, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->identityProvider = $identityProvider;
    }

    public function getByGithubRepositoryId(int $githubRepositoryId): GithubGitRepository
    {
        try {
            $repository = $this->entityManager->createQueryBuilder()
                ->from(GithubGitRepository::class, 'repository')
                ->select('repository')
                ->where('repository.githubRepositoryId = :githubRepositoryId')
                ->setParameter('githubRepositoryId', $githubRepositoryId)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $noResultException) {
            $repository = new GithubGitRepository($this->identityProvider->provide(), $githubRepositoryId);

            $this->entityManager->persist($repository);
            $this->entityManager->flush();
        }

        return $repository;
    }
}
