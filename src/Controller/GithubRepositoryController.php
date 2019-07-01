<?php declare (strict_types=1);

namespace Rector\RectorCI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubRepositoryController extends AbstractController
{
    /**
     * @Route("/app/github-repository", name="github_repository", methods={"GET"})
     */
    public function __invoke(): Response
    {
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
