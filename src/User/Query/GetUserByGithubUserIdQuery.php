<?php declare(strict_types=1);

namespace Rector\RectorCI\User\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Rector\RectorCI\Entity\User;
use Rector\RectorCI\User\Exceptions\UserNotFoundException;

final class GetUserByGithubUserIdQuery
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
     * @throws UserNotFoundException
     */
    public function query(int $githubUserId): User
    {
        try {
            return $this->entityManager->createQueryBuilder()
                ->from(User::class, 'user')
                ->select('user')
                ->where('user.githubUserId = :githubUserId')
                ->setParameter('githubUserId', $githubUserId)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $exception) {
            throw new UserNotFoundException();
        }
    }
}
