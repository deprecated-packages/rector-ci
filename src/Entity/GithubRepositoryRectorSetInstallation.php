<?php declare(strict_types=1);

namespace Rector\RectorCI\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class GithubRepositoryRectorSetInstallation
{
    /**
     * @var string
     */
    private const STATUS_PENDING = 'pending';

    /**
     * @var string
     */
    private const STATUS_ACTIVE = 'active';

    /**
     * @var GithubRepository
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="GithubRepository")
     */
    private $githubRepository;

    /**
     * @var RectorSet
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="RectorSet")
     */
    private $rectorSet;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $installedAt;

    /**
     * @ORM\Column
     * @var string
     */
    private $status;


    public function __construct(
        GithubRepository $githubRepository,
        RectorSet $rectorSet,
        DateTimeImmutable $installedAt
    ) {
        $this->githubRepository = $githubRepository;
        $this->rectorSet = $rectorSet;
        $this->installedAt = $installedAt;
        $this->status = self::STATUS_PENDING;
    }
}
