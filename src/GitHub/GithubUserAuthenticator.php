<?php declare (strict_types=1);

namespace Rector\RectorCI\GitHub;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Nette\Utils\Json;

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


    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }


    public function getAccessToken(string $code): string
    {
        $client = new Client();
        $response = $client->request('POST', 'https://github.com/login/oauth/access_token', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            RequestOptions::QUERY => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
            ]
        ]);

        $responseData = Json::decode($response->getBody()->getContents());

        // @TODO this might be an error:
        if (isset($responseData->error_description)) {
            throw new \RuntimeException($responseData->error_description);
        }

        $accessToken = $responseData->access_token;

        return $accessToken;
    }
}
