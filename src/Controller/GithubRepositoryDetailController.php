<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\Github\GithubRepositoryGetter;
use Rector\RectorCI\RectorSet\Query\GetRectorSetsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubRepositoryDetailController extends AbstractController
{
    /**
     * @var GetRectorSetsQuery
     */
    private $getRectorSetsQuery;

    /**
     * @var GithubRepositoryGetter
     */
    private $githubRepositoryGetter;


    public function __construct(
        GithubRepositoryGetter $githubRepositoryGetter,
        GetRectorSetsQuery $getRectorSetsQuery
    ) {
        $this->getRectorSetsQuery = $getRectorSetsQuery;
        $this->githubRepositoryGetter = $githubRepositoryGetter;
    }

    /**
     * @Route("/app/repository/github/{githubRepositoryId}", name="github_repository_detail", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');
        $this->githubRepositoryGetter->getGithubRepositoryOrCreateItIfNotExists($githubRepositoryId);

        // @TODO: check if organization has installation, if not, redirect user to github

        // @TODO: fetch repository from API, it might throw 403?
        // @TODO: fetch list of sets
        // @TODO: fetch activated sets for this repository

        return $this->render('githubRepository/githubRepositoryDetail.twig', [
            'sets' => $this->getRectorSetsQuery->query(),
            'installedSets' => [],
            'githubRepositoryId' => $githubRepositoryId,
        ]);
    }
}
