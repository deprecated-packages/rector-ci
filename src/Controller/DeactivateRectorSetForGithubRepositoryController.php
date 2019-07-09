<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\RectorSet\Exception\RectorSetNotActiveException;
use Rector\RectorCI\RectorSet\Exception\RectorSetNotFoundException;
use Rector\RectorCI\RectorSet\RectorSetDeactivator;
use Rector\RectorCI\Repository\GithubGitRepositoryRepository;
use Rector\RectorCI\Repository\RectorSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DeactivateRectorSetForGithubRepositoryController extends AbstractController
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
     * @var RectorSetDeactivator
     */
    private $rectorSetDeactivator;

    public function __construct(
        RectorSetDeactivator $rectorSetDeactivator,
        RectorSetRepository $rectorSetRepository,
        GithubGitRepositoryRepository $githubGitRepositoryRepository
    ) {
        $this->rectorSetRepository = $rectorSetRepository;
        $this->githubGitRepositoryRepository = $githubGitRepositoryRepository;
        $this->rectorSetDeactivator = $rectorSetDeactivator;
    }

    /**
     * @Route("/app/repository/github/{githubRepositoryId}/deactivate/{rectorSetName}", name="deactivate_set_github", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');
        $rectorSetName = $request->attributes->get('rectorSetName');

        try {
            $rectorSet = $this->rectorSetRepository->getByName($rectorSetName);
            $githubRepository = $this->githubGitRepositoryRepository->getByGithubRepositoryId($githubRepositoryId);

            $this->rectorSetDeactivator->deactivateSetForRepository($rectorSet, $githubRepository);
        } catch (RectorSetNotFoundException $rectorSetNotFoundException) {
            throw $this->createNotFoundException();
        } catch (RectorSetNotActiveException $rectorSetNotActiveException) {
            // .. Do nothing .. maybe we should show flash to user?
        }

        return $this->redirectToRoute('github_repository', [
            'githubRepositoryId' => $githubRepositoryId,
        ]);
    }
}
