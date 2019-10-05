<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\RectorSet\Exception\RectorSetNotActiveException;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotFoundException;
use Rector\RectorCI\RectorSet\GithubGitRepositoryRectorSetUninstaller;
use Rector\RectorCI\Repository\GithubGitRepositoryRepository;
use Rector\RectorCI\Repository\RectorSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UninstallRectorSetFromGithubRepositoryController extends AbstractController
{
    /**
     * @var RectorSetRepository
     */
    private $rectorSetRepository;

    /**
     * @var GithubGitRepositoryRepository
     */
    private $githubGitRepositoryRepository;

    /**
     * @var GithubGitRepositoryRectorSetUninstaller
     */
    private $rectorSetUninstaller;

    public function __construct(
        GithubGitRepositoryRectorSetUninstaller $rectorSetUninstaller,
        RectorSetRepository $rectorSetRepository,
        GithubGitRepositoryRepository $githubGitRepositoryRepository
    ) {
        $this->rectorSetRepository = $rectorSetRepository;
        $this->githubGitRepositoryRepository = $githubGitRepositoryRepository;
        $this->rectorSetUninstaller = $rectorSetUninstaller;
    }

    /**
     * @Route("/app/repository/github/{githubRepositoryId}/uninstall/{rectorSetName}", name="uninstall_set_github", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');
        $rectorSetName = $request->attributes->get('rectorSetName');

        try {
            $rectorSet = $this->rectorSetRepository->getByName($rectorSetName);
            $githubRepository = $this->githubGitRepositoryRepository->getByGithubRepositoryId($githubRepositoryId);

            $this->rectorSetUninstaller->uninstall($rectorSet, $githubRepository);
        } catch (RectorSetNotFoundException $rectorSetNotFoundException) {
            throw $this->createNotFoundException();
        } catch (RectorSetNotActiveException $rectorSetNotActiveException) {
            // .. Do nothing .. maybe we should show flash to user?
        }

        return $this->redirectToRoute('github_repository_detail', [
            'githubRepositoryId' => $githubRepositoryId,
        ]);
    }
}
