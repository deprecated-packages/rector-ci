<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\RectorSet\Exception\RectorSetAlreadyInstalledException;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotFoundException;
use Rector\RectorCI\RectorSet\GithubRepositoryRectorSetInstaller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class InstallRectorSetForGithubRepositoryController extends AbstractController
{
    /**
     * @var GithubRepositoryRectorSetInstaller
     */
    private $rectorSetInstaller;


    public function __construct(
        GithubRepositoryRectorSetInstaller $rectorSetInstaller
    ) {
        $this->rectorSetInstaller = $rectorSetInstaller;
    }

    /**
     * @Route("/app/repository/github/{githubRepositoryId}/install/{rectorSetName}", name="install_set_github", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');
        $rectorSetName = $request->attributes->get('rectorSetName');

        try {
            $this->rectorSetInstaller->install($rectorSetName, $githubRepositoryId);
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
