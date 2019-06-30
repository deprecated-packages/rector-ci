<?php declare(strict_types=1);

namespace Rector\RectorCI\User\Security;

use Rector\RectorCI\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

final class GithubAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var string
     */
    private const SESSION_NAME = 'github_auth';


    public function supports(Request $request): bool
    {
        $session = $request->getSession();

        if ($session) {
            return $session->has(self::SESSION_NAME);
        }

        return false;
    }

    public function getCredentials(Request $request): array
    {
        $accessToken = null;
        $session = $request->getSession();

        if ($session) {
            $accessToken = $session->get(self::SESSION_NAME);
        }

        return [
            'access_token' => $accessToken,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        return $userProvider->loadUserByUsername($credentials['access_token']);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $authenticationException): ?Response
    {
        throw new \LogicException('Not implemented yet');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        $session = $request->getSession();

        if ($session) {
            /** @var User $user */
            $user = $token->getUser();

            $session->set(self::SESSION_NAME, $user->getGithubAccessToken());
        }

        return null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    public function start(Request $request, ?AuthenticationException $authenticationException = null)
    {
        throw new \LogicException('Not implemented yet');
    }
}
