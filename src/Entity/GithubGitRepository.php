<?php declare(strict_types=1);

namespace Rector\RectorCI\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 */
class GithubGitRepository
{
    /**
     * @var UuidInterface
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $githubRepositoryId;


    public function __construct(UuidInterface $id, int $githubRepositoryId)
    {
        $this->id = $id;
        $this->githubRepositoryId = $githubRepositoryId;
    }


    public function getId(): UuidInterface
    {
        return $this->id;
    }


    public function getGithubRepositoryId(): int
    {
        return $this->githubRepositoryId;
    }
}
