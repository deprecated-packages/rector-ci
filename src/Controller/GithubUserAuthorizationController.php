<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use League\OAuth2\Client\Token\AccessToken;
use Rector\RectorCI\Exception\GitHub\GitHubAuthenticationException;
use League\OAuth2\Client\Provider\Github as GithubProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

final class GithubUserAuthorizationController extends AbstractController
{
    /**
     * @var string
     */
    private const STATE_SESSION_NAME = 'githubOauth2State';

    /**
     * @var GithubProvider
     */
    private $githubProvider;

    /**
     * @var SessionInterface
     */
    private $session;


    public function __construct(GithubProvider $githubProvider, SessionInterface $session)
    {
        $this->githubProvider = $githubProvider;
        $this->session = $session;
    }

    /**
     * @Route("/authorization/github", name="github_authorization", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $code = $request->query->get('code');

        if (!$code) {
            $authUrl = $this->githubProvider->getAuthorizationUrl();
            $this->session->set(self::STATE_SESSION_NAME, $this->githubProvider->getState());

            return $this->redirect($authUrl);
        }

        $state = $request->query->get('state');

        if (!$state || $state !== $this->session->get(self::STATE_SESSION_NAME)) {
            $this->session->remove(self::STATE_SESSION_NAME);

            throw new GitHubAuthenticationException('Invalid state!');
        }

        /** @var AccessToken $token */
        $token = $this->githubProvider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        $user = $this->githubProvider->getResourceOwner($token);
        $userId = $user->getId();

        // @TODO: if user Entity does not exist, create him
        // @TODO: save user id or something to sessions -> make user with his entity logged in

        return $this->redirectToRoute('dashboard');
    }
}
