<?php declare(strict_types=1);

namespace Rector\RectorCI\GitHub;

use Github\Client;

final class GithubInstallationAuthenticator
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function authenticate(int $installationId): string
    {
        $token = $this->client->apps()->createInstallationToken($installationId);
        $accessToken = $token['token'];

        $this->client->authenticate($accessToken, null, Client::AUTH_HTTP_TOKEN);

        return $accessToken;
    }
}
