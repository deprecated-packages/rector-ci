<?php declare(strict_types=1);

namespace Rector\RectorCI\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Rector\RectorCI\Entity\User;
use Rector\RectorCI\User\Exceptions\UserNotFoundException;

final class UserRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function userWithGithubIdExists(int $githubUserId): bool
    {
        return (bool) $this->entityManager->createQueryBuilder()
            ->from(User::class, 'user')
            ->select('COUNT(user)')
            ->where('user.githubUserId = :githubUserId')
            ->setParameter('githubUserId', $githubUserId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws UserNotFoundException
     */
    public function getUserByGithubId(int $githubUserId): User
    {
        if (! $this->userWithGithubIdExists($githubUserId)) {
            throw new UserNotFoundException();
        }

        return $this->entityManager->createQueryBuilder()
            ->from(User::class, 'user')
            ->select('user')
            ->where('user.githubUserId = :githubUserId')
            ->setParameter('githubUserId', $githubUserId)
            ->getQuery()
            ->getSingleResult();
    }
}
