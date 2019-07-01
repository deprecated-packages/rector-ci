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
        return $this->render('githubRepository/githubRepository.twig');
    }
}
