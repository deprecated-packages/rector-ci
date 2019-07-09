<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\RectorSet\Exception\RectorSetAlreadyActivatedException;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotFoundException;
use Rector\RectorCI\RectorSet\RectorSetActivator;
use Rector\RectorCI\Repository\GithubGitRepositoryRepository;
use Rector\RectorCI\Repository\RectorSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ActivateRectorSetForGithubRepositoryController extends AbstractController
{
    /**
     * @var RectorSetActivator
     */
    private $rectorSetActivator;

    /**
     * @var RectorSetRepository
     */
    private $rectorSetRepository;

    /**
     * @var GithubGitRepositoryRepository
     */
    private $githubGitRepositoryRepository;


    public function __construct(
        RectorSetActivator $rectorSetActivator,
        RectorSetRepository $rectorSetRepository,
        GithubGitRepositoryRepository $githubGitRepositoryRepository
    )
    {
        $this->rectorSetActivator = $rectorSetActivator;
        $this->rectorSetRepository = $rectorSetRepository;
        $this->githubGitRepositoryRepository = $githubGitRepositoryRepository;
    }


    /**
     * @Route("/app/repository/github/{githubRepositoryId}/activate/{rectorSetName}", name="activate_set_github", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');
        $rectorSetName = $request->attributes->get('rectorSetName');

        try {
            $rectorSet = $this->rectorSetRepository->getByName($rectorSetName);
            $githubRepository = $this->githubGitRepositoryRepository->getByGithubRepositoryId($githubRepositoryId);

            $this->rectorSetActivator->activateForRepository($githubRepository, $rectorSet);
        } catch (RectorSetNotFoundException $exception) {
            throw $this->createNotFoundException();
        } catch (RectorSetAlreadyActivatedException $exception) {
            // .. Do nothing .. maybe we should show flash to user?
        }

        return $this->redirectToRoute('github_repository', [
           'githubRepositoryId' => $githubRepositoryId,
        ]);
    }
}
