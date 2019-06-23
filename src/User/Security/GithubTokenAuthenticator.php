<?php declare(strict_types=1);

namespace Rector\RectorCI\User\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Documentation @see https://symfonycasts.com/screencast/symfony-security/api-token-authenticator
 *
 * Real implementation @see https://www.nielsvandermolen.com/symfony-4-api-platform-application/
 * ↓
 * @see https://github.com/nielsvandermolen/example_symfony4_api/blob/d23edca397f7e7bde35a639c1ed6ad1b132286a8/project/src/Security/TokenAuthenticator.php
 */
final class GithubTokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @todo What is the header name?
     * @var string
     */
    private const TOKEN_HEADER = 'X-AUTH-TOKEN';

    /**
     * @var string
     */
    private const TOKEN_KEY = 'token';

    public function supports(Request $request): bool
    {
        return $request->headers->has(self::TOKEN_HEADER);
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array).
     *
     * Whatever value you return here will be passed to getUser() and checkCredentials()
     * For an API token that's on a header, you might use:
     *
     *      return ['api_key' => $request->headers->get('X-API-TOKEN')];
     */
    public function getCredentials(Request $request): array
    {
        return [
            self::TOKEN_KEY => $request->headers->get(self::TOKEN_HEADER),
        ];
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed $credentials
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        $apiKey = $credentials[self::TOKEN_KEY];
        if ($apiKey === null) {
            return null;
        }

        // if a User object, checkCredentials() is called
        return $userProvider->loadUserByUsername($apiKey);
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If any value other than true is returned, authentication will
     * fail. You may also throw an AuthenticationException if you wish
     * to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param mixed $credentials
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @param Request $request
     * @param AuthenticationException $authenticationException
     */
    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $authenticationException
    ): ?Response {
        $data = [
            'message' => strtr($authenticationException->getMessageKey(), $authenticationException->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication executed and was successful!
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @param string $providerKey The provider (i.e. firewall) key
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        // on success, let the request continue
        return null;
    }

    /**
     * Does this method support remember me cookies?
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }

    public function start(Request $request, ?AuthenticationException $authenticationException = null)
    {
        return new Response('Auth header required', 401);
    }
}
