<?php declare(strict_types=1);

namespace Rector\RectorCI\GitRepository;

final class GitRepositoryPathGetter
{
    /**
     * @var string
     */
    private $repositoriesDirectory;


    public function __construct(string $repositoriesDirectory)
    {
        $this->repositoriesDirectory = $repositoriesDirectory;
    }


    public function get(string $repositoryName): string
    {
        return $this->repositoriesDirectory . '/' . $repositoryName;
    }
}
