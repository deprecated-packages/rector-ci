<?php declare (strict_types=1);

namespace RectorCI\Controller;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GitHubWebHookController
{
    /**
     * @Route("/web-hooks/github", methods={"POST"})
     */
    public function __invoke(): Response
    {
        $client = new Client();

        // 1. Authenticating as a GitHub App - https://developer.github.com/apps/building-github-apps/authenticating-with-github-apps/#authenticating-as-a-github-app
        $privateKey = file_get_contents(__DIR__ . '/../../config/keys/rector-ci.pem');
        $token = array(
            'iss' => getenv('GITHUB_APP_ID'),
            'exp' => time() + (10 * 60),
            'iat' => time(),
        );

        $jwt = JWT::encode($token, $privateKey, 'RS256');

        // curl -i -H "Authorization: Bearer YOUR_JWT" -H "Accept: application/vnd.github.machine-man-preview+json" https://api.github.com/app
        $response = $client->request('GET', 'https://api.github.com/app', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.machine-man-preview+json',
                'Authorization' => sprintf('Bearer %s', $jwt),
            ]
        ]);

        $response = $response->getBody()->getContents();

        // 2. Authenticating as an installation - https://developer.github.com/apps/building-github-apps/authenticating-with-github-apps/#authenticating-as-an-installation

        // 3. git clone https://x-access-token:<token>@github.com/owner/repo.git

        return new Response($response);
    }
}
