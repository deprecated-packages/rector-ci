<?php declare(strict_types=1);

namespace Rector\RectorCI\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RectorSetActivation
{
    /**
     * @var GithubGitRepository
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="GithubGitRepository")
     */
    private $githubGitRepository;

    /**
     * @var RectorSet
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="RectorSet")
     */
    private $rectorSet;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $activatedAt;


    public function __construct(
        GithubGitRepository $githubGitRepository,
        RectorSet $rectorSet,
        \DateTimeImmutable $activatedAt
    )
    {
        $this->githubGitRepository = $githubGitRepository;
        $this->rectorSet = $rectorSet;
        $this->activatedAt = $activatedAt;
    }
}
