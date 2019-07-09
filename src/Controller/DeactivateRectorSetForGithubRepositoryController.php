<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DeactivateRectorSetForGithubRepositoryController extends AbstractController
{
    /**
     * @Route("/app/repository/github/{githubRepositoryId}/deactivate/{rectorSetName}", name="deactivate_set_github", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $githubRepositoryId = (int) $request->attributes->get('githubRepositoryId');

        return $this->redirectToRoute('github_repository', [
            'githubRepositoryId' => $githubRepositoryId,
        ]);
    }
}
