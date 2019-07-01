<?php declare(strict_types=1);

namespace Rector\RectorCI\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Rector\RectorCI\User\Security\UserRole;
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
     * @var int
     */
    private $githubUserId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $githubAccessToken;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $isBetaTester;


    public function __construct(UuidInterface $id, int $githubUserId)
    {
        $this->id = $id;
        $this->githubUserId = $githubUserId;
        $this->isBetaTester = false;
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
        $roles = [UserRole::USER];

        if ($this->isBetaTester) {
            $roles[] = UserRole::BETA_TESTER;
        }

        return $roles;
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


    public function getGithubUserId(): int
    {
        return $this->githubUserId;
    }
}
