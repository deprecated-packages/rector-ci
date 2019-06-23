<?php declare(strict_types=1);

namespace Rector\RectorCI\GitHub;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Nette\Utils\Json;
use Rector\RectorCI\Exception\GitHub\GitHubAuthenticationException;

final class GithubUserAuthenticator
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client, string $clientId, string $clientSecret)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getAccessToken(string $code): string
    {
        $response = $this->client->request('POST', 'https://github.com/login/oauth/access_token', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            RequestOptions::QUERY => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
            ],
        ]);

        $responseData = Json::decode($response->getBody()->getContents());

        // @TODO this might be an error:
        if (isset($responseData->error_description)) {
            throw new GitHubAuthenticationException($responseData->error_description);
        }

        return $responseData->access_token;
    }
}
