<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\RectorSet\Exception\RectorSetAlreadyInstalledException;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotFoundException;
use Rector\RectorCI\RectorSet\GithubGitRepositoryRectorSetInstaller;
use Rector\RectorCI\Repository\GithubGitRepositoryRepository;
use Rector\RectorCI\Repository\RectorSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class InstallRectorSetForGithubRepositoryController extends AbstractController
{
    /**
     * @var GithubGitRepositoryRectorSetInstaller
     */
    private $rectorSetInstaller;

    /**
     * @var RectorSetRepository
     */
    private $rectorSetRepository;

    /**
     * @var GithubGitRepositoryRepository
     */
    private $githubGitRepositoryRepository;

    public function __construct(
        GithubGitRepositoryRectorSetInstaller $rectorSetInstaller,
        RectorSetRepository $rectorSetRepository,
        GithubGitRepositoryRepository $githubGitRepositoryRepository
    ) {
        $this->rectorSetInstaller = $rectorSetInstaller;
        $this->rectorSetRepository = $rectorSetRepository;
        $this->githubGitRepositoryRepository = $githubGitRepositoryRepository;
    }

    /**
     * @Route("/app/repository/github/{githubRepositoryId}/install/{rectorSetName}", name="install_set_github", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');
        $rectorSetName = $request->attributes->get('rectorSetName');

        try {
            $rectorSet = $this->rectorSetRepository->getByName($rectorSetName);
            $githubRepository = $this->githubGitRepositoryRepository->getByGithubRepositoryId($githubRepositoryId);

            $this->rectorSetInstaller->install($rectorSet, $githubRepository);
        } catch (RectorSetNotFoundException $rectorSetNotFoundException) {
            throw $this->createNotFoundException();
        } catch (RectorSetAlreadyInstalledException $rectorSetAlreadyActivatedException) {
            // .. Do nothing .. maybe we should show flash to user?
        }

        return $this->redirectToRoute('github_repository_detail', [
            'githubRepositoryId' => $githubRepositoryId,
        ]);
    }
}
