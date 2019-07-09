<?php declare (strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\RectorSet\RectorSetActivationChecker;
use Rector\RectorCI\Repository\GithubGitRepositoryRepository;
use Rector\RectorCI\Repository\RectorSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubRepositoryController extends AbstractController
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
     * @var RectorSetActivationChecker
     */
    private $rectorSetActivationChecker;


    public function __construct(
        RectorSetRepository $rectorSetRepository,
        GithubGitRepositoryRepository $githubGitRepositoryRepository,
        RectorSetActivationChecker $rectorSetActivationChecker
    )
    {
        $this->rectorSetRepository = $rectorSetRepository;
        $this->githubGitRepositoryRepository = $githubGitRepositoryRepository;
        $this->rectorSetActivationChecker = $rectorSetActivationChecker;
    }

    /**
     * @Route("/app/repository/github/{githubRepositoryId}", name="github_repository", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');

        // @TODO: check if organization has installation, if not, redirect user to github

        // @TODO: fetch repository from API, it might throw 403?
        // @TODO: fetch list of sets
        // @TODO: fetch activated sets for this repository

        return $this->render('githubRepository/githubRepository.twig', [
            'sets' => $this->rectorSetRepository->findAll(),
            'gitRepository' => $this->githubGitRepositoryRepository->getByGithubRepositoryId($githubRepositoryId),
            'activationChecker' => $this->rectorSetActivationChecker,
        ]);
    }
}
