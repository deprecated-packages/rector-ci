<?php declare(strict_types=1);

namespace Rector\RectorCI\GitRepository;

use Symfony\Component\Process\Process;

final class GitRepositoryDownloader
{
    /**
     * @var GitRepositoryPathGetter
     */
    private $gitRepositoryPathGetter;


    public function __construct(GitRepositoryPathGetter $gitRepositoryPathGetter)
    {
        $this->gitRepositoryPathGetter = $gitRepositoryPathGetter;
    }


    public function prepareRepositoryToCommit(string $repository, string $accessToken, string $commit): void
    {
        $repositoryDirectory = $this->gitRepositoryPathGetter->get($repository);

        if (! file_exists($repositoryDirectory)) {
            $this->cloneRepository($repository, $repositoryDirectory, $accessToken);
        } else {
            $this->discardLocalChanges($repositoryDirectory);
            $this->fetchRemoteChanges($repositoryDirectory);
        }

        $this->checkoutToCommit($repositoryDirectory, $commit);
        $this->installComposer($repositoryDirectory);
    }


    /**
     * @TODO: Maybe this should be somewhere else because of the access token variable?
     */
    private function cloneRepository(string $repository, string $repositoryDirectory, string $accessToken): void
    {
        $cloneUrl = sprintf('https://x-access-token:%s@github.com/%s.git', $accessToken, $repository);

        $cloneProcess = new Process(['git', 'clone', $cloneUrl, $repositoryDirectory]);

        $cloneProcess->setTimeout(null);
        $cloneProcess->mustRun();
    }


    private function discardLocalChanges(string $repositoryDirectory): void
    {
        $gitCheckoutChangesProcess = new Process(['git', 'checkout', '-f'], $repositoryDirectory);

        $gitCheckoutChangesProcess->mustRun();
    }


    private function fetchRemoteChanges(string $repositoryDirectory): void
    {
        $gitFetchProcess = new Process(['git', 'fetch', '-p'], $repositoryDirectory);

        $gitFetchProcess->mustRun();
    }


    private function checkoutToCommit(string $repositoryDirectory, string $commit): void
    {
        $gitCheckoutHeadProcess = new Process([
            'git',
            'checkout',
            $commit,
        ], $repositoryDirectory);

        $gitCheckoutHeadProcess->mustRun();
    }


    private function installComposer(string $repositoryDirectory): void
    {
        $composerInstallProcess = new Process(['composer', 'install'], $repositoryDirectory);

        $composerInstallProcess->setTimeout(null);
        $composerInstallProcess->mustRun();
    }
}
