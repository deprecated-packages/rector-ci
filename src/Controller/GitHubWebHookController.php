<?php declare (strict_types=1);

namespace RectorCI\Controller;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GitHubWebHookController
{
    /**
     * @Route("/web-hooks/github", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        // TODO: Validate if event is check_suite
        //$request->headers->get('X-Github-Event')

        // @TODO!! Very important! Prevent circular reference, only commits by human

        $client = new Client();

        $privateKey = file_get_contents(__DIR__ . '/../../config/keys/rector-ci.pem');
        $token = array(
            'iss' => getenv('GITHUB_APP_ID'),
            'exp' => time() + (10 * 60),
            'iat' => time(),
        );

        $data = Json::decode($request->getContent());
        $installationId = $data->installation->id;

        $jwt = JWT::encode($token, $privateKey, 'RS256');

        $response = $client->request('POST', "https://api.github.com/app/installations/$installationId/access_tokens", [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.machine-man-preview+json',
                'Authorization' => sprintf('Bearer %s', $jwt),
            ]
        ]);

        $responseData = Json::decode($response->getBody()->getContents());
        $cloneToken = $responseData->token;
        $repositoryName = $data->repository->full_name;
        $cloneUrl = $data->repository->clone_url;
        $cloneUrl = sprintf('https://x-access-token:%s@', $cloneToken) . Strings::after($cloneUrl, 'https://');

        $originalBranch = $data->check_suite->head_branch;
        $newBranch = $originalBranch . '-rector';

        shell_exec("git clone $cloneUrl ../repositories/$repositoryName");
        shell_exec("cd ../repositories/$repositoryName && ../../../bin/script.sh $originalBranch $newBranch");

        // @TODO now it commits as user, maybe github app should commit instead??

        $client = new Client();
        $client->request('POST', "https://api.github.com/repos/$repositoryName/pulls", [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $cloneToken),
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => Json::encode($body = [
                'title' => 'Rector - Fix',
                'head' => $newBranch,
                'base' => $originalBranch,
                'body' => 'Rector automated pull request',
            ]),
        ]);

        return new Response($cloneUrl);
    }
}
