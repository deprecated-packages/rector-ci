<?php declare (strict_types=1);

namespace Rector\RectorCI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubRepositoryController extends AbstractController
{
    /**
     * @Route("/app/repository/github/{githubRepositoryId}", name="github_repository", methods={"GET"})
     */
    public function __invoke(): Response
    {
        // @TODO: check if organization has installation, if not, redirect user to github

        // @TODO: fetch repository from API, it might throw 403?
        // @TODO: fetch list of sets
        // @TODO: fetch activated sets for this repository

        $sets = [];

        for ($i=0 ; $i<=10 ; $i++) {
            $sets[] = [
                'id' => 'set' . $i,
                'name' => 'My set ' . $i,
                'description' => 'Lorem ipsum dolor sit amet',
                'isActivated' => $i%3 === 0,
            ];
        }

        return $this->render('githubRepository/githubRepository.twig', [
            'sets' => $sets,
        ]);
    }
}
