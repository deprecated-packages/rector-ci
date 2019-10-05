<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Github as GithubProvider;
use League\OAuth2\Client\Token\AccessToken;
use Ramsey\Uuid\Uuid;
use Rector\RectorCI\Entity\User;
use Rector\RectorCI\Github\Exceptions\GithubAuthenticationException;
use Rector\RectorCI\Repository\UserRepository;
use Rector\RectorCI\User\Exceptions\UserNotFoundException;
use Rector\RectorCI\User\Security\GithubAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

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

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GuardAuthenticatorHandler
     */
    private $guardAuthenticatorHandler;

    /**
     * @var GithubAuthenticator
     */
    private $githubAuthenticator;

    public function __construct(
        GithubProvider $githubProvider,
        SessionInterface $session,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        GithubAuthenticator $githubAuthenticator,
        GuardAuthenticatorHandler $guardAuthenticatorHandler
    ) {
        $this->githubProvider = $githubProvider;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->guardAuthenticatorHandler = $guardAuthenticatorHandler;
        $this->githubAuthenticator = $githubAuthenticator;
    }

    /**
     * @Route("/authorization/github", name="github_authorization", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $code = $request->query->get('code');

        if (! $code) {
            $authUrl = $this->githubProvider->getAuthorizationUrl();
            $this->session->set(self::STATE_SESSION_NAME, $this->githubProvider->getState());

            return $this->redirect($authUrl);
        }

        $state = $request->query->get('state');

        if ($this->session->has(self::STATE_SESSION_NAME) && $state !== $this->session->get(self::STATE_SESSION_NAME)) {
            $this->clearState();

            throw new GithubAuthenticationException('Invalid state!');
        }

        /** @var AccessToken $token */
        $token = $this->githubProvider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        $githubUser = $this->githubProvider->getResourceOwner($token);
        $githubUserId = $githubUser->getId();

        try {
            $user = $this->userRepository->getUserByGithubId($githubUserId);
        } catch (UserNotFoundException $userNotFoundException) {
            $user = $this->createUserFromGithub($githubUserId);
        }

        $user->updateGithubAccessToken($token->getToken());

        $this->entityManager->flush();

        $this->clearState();

        $this->guardAuthenticatorHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $this->githubAuthenticator,
            'app'
        );

        return $this->redirectToRoute('dashboard');
    }

    private function clearState(): void
    {
        $this->session->remove(self::STATE_SESSION_NAME);
    }

    private function createUserFromGithub(int $githubUserId): User
    {
        $user = new User(Uuid::uuid4(), $githubUserId);

        $this->entityManager->persist($user);

        return $user;
    }
}
