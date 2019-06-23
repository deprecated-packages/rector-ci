<?php declare (strict_types=1);

namespace Rector\RectorCI\Messenger;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PrepareProjectRepositoryHandler implements MessageHandlerInterface
{
    public function __invoke(PrepareProjectRepositoryCommand $command): void
    {
        $originalBranch = $webhookData->check_suite->head_branch;
        $newBranch = 'rectified/' . $originalBranch;
        $repositoryFullName = $webhookData->repository->full_name;
        $username = $webhookData->repository->owner->login;
        $repositoryName = $webhookData->repository->name;


        $repositoryDirectory = __DIR__ . '/../../repositories/' . $repositoryFullName;

        if (! file_exists($repositoryDirectory)) {
            $this->cloneRepository($repositoryFullName, $accessToken, $repositoryDirectory);
        }

        $gitCheckoutChangesProcess = new Process(['git', 'checkout', '-f'], $repositoryDirectory);
        $gitCheckoutChangesProcess->mustRun();

        $gitFetchProcess = new Process(['git', 'fetch', '-p'], $repositoryDirectory);
        $gitFetchProcess->mustRun();

        $gitCheckoutHeadProcess = new Process([
            'git',
            'checkout',
            $webhookData->check_suite->head_sha,
        ], $repositoryDirectory);
        $gitCheckoutHeadProcess->mustRun();

        $composerInstallProcess = new Process(['composer', 'install'], $repositoryDirectory);
        $composerInstallProcess->setTimeout(null);
        $composerInstallProcess->mustRun();
    }
}
