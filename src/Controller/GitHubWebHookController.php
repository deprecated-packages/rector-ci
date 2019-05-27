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

        $webhookData = Json::decode($request->getContent());
        $installationId = $webhookData->installation->id;
        $repositoryName = $webhookData->repository->full_name;

        $jwt = JWT::encode($token, $privateKey, 'RS256');

        $accessTokenResponse = $client->request('POST', "https://api.github.com/app/installations/$installationId/access_tokens", [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.machine-man-preview+json',
                'Authorization' => sprintf('Bearer %s', $jwt),
            ]
        ]);

        $accessTokenResponseData = Json::decode($accessTokenResponse->getBody()->getContents());
        $accessToken = $accessTokenResponseData->token;

        $cloneUrl = sprintf('https://x-access-token:%s@', $accessToken) . Strings::after($webhookData->repository->clone_url, 'https://');

        $originalBranch = $webhookData->check_suite->head_branch;
        $newBranch = $originalBranch . '-rector';

        if (!is_file("../repositories/$repositoryName")) {
            // shell_exec("git clone $cloneUrl ../repositories/$repositoryName");
            // shell_exec("cd ../repositories/$repositoryName && ../../../bin/script.sh $originalBranch $newBranch");
        } else {

        }

        // 1. Create blob
        $blobUrl = str_replace('{/sha}', '', $webhookData->repository->blobs_url);
        $blobResponse = $client->request('POST', $blobUrl, [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $accessToken),
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => Json::encode($body = [
                'content' => 'Hello world',
            ]),
        ]);
        $blobResponseData = Json::decode($blobResponse->getBody()->getContents());
        $blobSha = $blobResponseData->sha;

        // 2. Create tree
        $originalTreeSha = $webhookData->check_suite->head_commit->tree_id;
        $treeUrl = str_replace('{/sha}', '', $webhookData->repository->trees_url);
        $treeResponse = $client->request('POST', $treeUrl, [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $accessToken),
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => Json::encode($body = [
                'base_tree' => $originalTreeSha,
                'tree' => [
                    [
                        'path' => 'some-random-file.txt',
                        'mode' => '100644',
                        'type' => 'blob',
                        'sha' => $blobSha,
                    ]
                ],
            ]),
        ]);
        $treeResponseData = Json::decode($treeResponse->getBody()->getContents());
        $treeSha = $treeResponseData->sha;

        // 3. Create commit
        $originalCommitSha = $webhookData->check_suite->head_commit->id;
        $commitUrl = str_replace('{/sha}', '', $webhookData->repository->git_commits_url);
        $commitResponse = $client->request('POST', $commitUrl, [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $accessToken),
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => Json::encode($body = [
                'message' => $originalTreeSha,
                'parents' => [$originalCommitSha],
                'tree' => $treeSha,
            ]),
        ]);
        $commitResponseData = Json::decode($commitResponse->getBody()->getContents());
        $commitSha = $commitResponseData->sha;

        // 4. Create reference
        $referenceUrl = str_replace('{/sha}', '', $webhookData->repository->git_refs_url);
        $client->request('POST', $referenceUrl, [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $accessToken),
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => Json::encode($body = [
                'ref' => 'refs/heads/' . $newBranch,
                'sha' => $commitSha,
            ]),
        ]);

        // 5. Create pull request
        $client->request('POST', "https://api.github.com/repos/$repositoryName/pulls", [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $accessToken),
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => Json::encode([
                'title' => 'Rector - Fix',
                'head' => $newBranch,
                'base' => $originalBranch,
                'body' => 'Rector automated pull request',
            ]),
        ]);

    }
}
