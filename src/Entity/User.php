<?php declare(strict_types=1);

namespace Rector\RectorCI\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity()
 */
final class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     * @var string
     */
    private $githubUserId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $githubAccessToken;


    public function __construct(UuidInterface $id, int $githubUserId)
    {
        $this->id = $id;
        $this->githubUserId = $githubUserId;
    }


    public function updateGithubAccessToken(string $githubAccessToken): void
    {
        $this->githubAccessToken = $githubAccessToken;
    }


    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }


    public function getPassword(): string
    {
        return '';
    }


    public function getSalt(): ?string
    {
        return null;
    }


    public function getUsername(): string
    {
        return $this->id->toString();
    }


    public function eraseCredentials(): void
    {
    }


    public function getGithubAccessToken(): string
    {
        return $this->githubAccessToken;
    }
}
