<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Rector\RectorCI\Exception\GitHub\GitHubAuthorizationException;
use Rector\RectorCI\GitHub\GithubUserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubUserAuthorizationController extends AbstractController
{
    /**
     * @var GithubUserAuthenticator
     */
    private $githubUserAuthenticator;

    public function __construct(GithubUserAuthenticator $githubUserAuthenticator)
    {
        $this->githubUserAuthenticator = $githubUserAuthenticator;
    }

    /**
     * @Route("/authorization/github", name="github_autorization", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $code = $request->query->get('code');

        // @TODO: state verification

        if (! $code) {
            throw new GitHubAuthorizationException('Code is missing');
        }

        $accessToken = $this->githubUserAuthenticator->getAccessToken($code);

        return $this->redirectToRoute('dashboard');
    }
}
