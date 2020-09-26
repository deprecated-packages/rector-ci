<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\RectorSet\Exception\RectorSetNotInstalledException;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotFoundException;
use Rector\RectorCI\RectorSet\GithubRepositoryRectorSetUninstaller;
use Rector\RectorCI\RectorSet\Query\GetRectorSetByNameQuery;
use Rector\RectorCI\Repository\GithubRepositoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UninstallRectorSetFromGithubRepositoryController extends AbstractController
{
    /**
     * @var GithubRepositoryRepository
     */
    private $githubRepositoryRepository;

    /**
     * @var GithubRepositoryRectorSetUninstaller
     */
    private $rectorSetUninstaller;

    /**
     * @var GetRectorSetByNameQuery
     */
    private $getRectorSetByNameQuery;


    public function __construct(
        GithubRepositoryRectorSetUninstaller $rectorSetUninstaller,
        // GithubRepositoryRepository $githubRepositoryRepository,
        GetRectorSetByNameQuery $getRectorSetByNameQuery
    ) {
        // $this->githubRepositoryRepository = $githubRepositoryRepository;
        $this->rectorSetUninstaller = $rectorSetUninstaller;
        $this->getRectorSetByNameQuery = $getRectorSetByNameQuery;
    }

    /**
     * @Route("/app/repository/github/{githubRepositoryId}/uninstall/{rectorSetName}", name="uninstall_set_github", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');
        $rectorSetName = $request->attributes->get('rectorSetName');

        try {
            $this->rectorSetUninstaller->uninstall($rectorSetName, $githubRepositoryId);
        } catch (RectorSetNotFoundException $rectorSetNotFoundException) {
            throw $this->createNotFoundException();
        } catch (RectorSetNotInstalledException $rectorSetNotActiveException) {
            // .. Do nothing .. maybe we should show flash to user?
        }

        return $this->redirectToRoute('github_repository_detail', [
            'githubRepositoryId' => $githubRepositoryId,
        ]);
    }
}
