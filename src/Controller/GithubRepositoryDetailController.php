<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\Repository\GithubRepositoryRepository;
use Rector\RectorCI\Repository\RectorSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubRepositoryDetailController extends AbstractController
{
    /**
     * @var RectorSetRepository
     */
    private $rectorSetRepository;

    /**
     * @var GithubRepositoryRepository
     */
    private $githubRepositoryRepository;


    public function __construct(
        RectorSetRepository $rectorSetRepository,
        GithubRepositoryRepository $githubRepositoryRepository
    ) {
        $this->rectorSetRepository = $rectorSetRepository;
        $this->githubRepositoryRepository = $githubRepositoryRepository;
    }

    /**
     * @Route("/app/repository/github/{githubRepositoryId}", name="github_repository_detail", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');

        // @TODO: check if organization has installation, if not, redirect user to github

        // @TODO: fetch repository from API, it might throw 403?
        // @TODO: fetch list of sets
        // @TODO: fetch activated sets for this repository

        return $this->render('githubRepository/githubRepositoryDetail.twig', [
            'sets' => $this->rectorSetRepository->findAll(),
            'installedSets' => [],
            'githubRepositoryId' => $githubRepositoryId,
        ]);
    }
}
