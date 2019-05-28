<?php declare (strict_types=1);

namespace RectorCI\Controller;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

final class GitHubWebHookController
{
    /**
     * @Route("/web-hooks/github", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        $event = $request->headers->get('X-Github-Event');

        if ($event !== 'check_suite') {
            return new Response('Non check_suite event', Response::HTTP_ACCEPTED);
        }

        $webhookData = Json::decode($request->getContent());

        if ($webhookData->sender->type === 'Bot') {
            return new Response('Not reacting to commits by bots', Response::HTTP_NOT_MODIFIED);
        }

        $originalBranch = $webhookData->check_suite->head_branch;
        $newBranch = $originalBranch . '-rector';

        $client = new Client();

        $privateKey = file_get_contents(__DIR__ . '/../../config/keys/rector-ci.pem');
        $token = array(
            'iss' => getenv('GITHUB_APP_ID'),
            'exp' => time() + (10 * 60),
            'iat' => time(),
        );

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

        // TODO: Create github check

        $cloneUrl = sprintf('https://x-access-token:%s@', $accessToken) . Strings::after($webhookData->repository->clone_url, 'https://');
        $repositoryDirectory = __DIR__ . '/../../repositories/' . $repositoryName;

        if (! file_exists("../repositories/$repositoryName")) {
            $cloneProcess = new Process(['git', 'clone', $cloneUrl, $repositoryName, $repositoryDirectory]);
            $cloneProcess->mustRun();
        }

        $gitCheckoutChangesProcess = new Process(['git', 'checkout', '-f'], $repositoryDirectory);
        $gitCheckoutChangesProcess->mustRun();

        $gitFetchProcess = new Process(['git', 'fetch', '-p'], $repositoryDirectory);
        $gitFetchProcess->mustRun();

        $gitCheckoutHeadProcess = new Process(['git', 'checkout', sprintf('origin/%s', $originalBranch)], $repositoryDirectory);
        $gitCheckoutHeadProcess->mustRun();

        $composerInstallProcess = new Process(['composer', 'install'], $repositoryDirectory);
        $composerInstallProcess->mustRun();

        $rectorProcess = new Process(['vendor/bin/rector', 'process', 'src', '--output-format=json'], $repositoryDirectory, [
            'APP_ENV' => false,
            'APP_DEBUG' => false,
            'SYMFONY_DOTENV_VARS' => false,
        ]);
        $rectorProcess->mustRun();

        $rectorProcessOutput = Json::decode($rectorProcess->getOutput());
        $changedFilesPaths = $rectorProcessOutput->changed_files;
        $blobShas = [];

        // TODO: decide if something was changed or not
        // TODO: if not, skip committing and creating PR

        // 1. Create blobs
        $blobUrl = str_replace('{/sha}', '', $webhookData->repository->blobs_url);

        foreach ($changedFilesPaths as $index => $changedFilePath) {
            $blobResponse = $client->request('POST', $blobUrl, [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/vnd.github.v3+json',
                    'Authorization' => sprintf('Token %s', $accessToken),
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::BODY => Json::encode($body = [
                    'content' => file_get_contents($repositoryDirectory . '/' . $changedFilePath),
                ]),
            ]);
            $blobResponseData = Json::decode($blobResponse->getBody()->getContents());
            $blobShas[$changedFilePath] = $blobResponseData->sha;
        }

        // 2. Create tree
        $originalTreeSha = $webhookData->check_suite->head_commit->tree_id;
        $treeUrl = str_replace('{/sha}', '', $webhookData->repository->trees_url);
        $tree = [];

        foreach ($blobShas as $filePath => $blobSha) {
            $tree[] = [
                'path' => $filePath,
                'mode' => '100644',
                'type' => 'blob',
                'sha' => $blobSha,
            ];
        }

        $treeResponse = $client->request('POST', $treeUrl, [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $accessToken),
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => Json::encode($body = [
                'base_tree' => $originalTreeSha,
                'tree' => $tree,
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
                'message' => 'Rulling the wolrd via Rector!',
                'parents' => [$originalCommitSha],
                'tree' => $treeSha,
            ]),
        ]);
        $commitResponseData = Json::decode($commitResponse->getBody()->getContents());
        $commitSha = $commitResponseData->sha;

        // TODO: Force push?
        // 4. Create reference
        $referenceUrl = str_replace('{/sha}', '', $webhookData->repository->git_refs_url);

        try {
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
        } catch (ClientException $e) {
            // Update reference, because it already exists
            if ($e->getCode() === 422) {
                $referenceUrl = str_replace('{/sha}', 'heads/' . $newBranch, $webhookData->repository->git_refs_url);

                $client->request('PATCH', $referenceUrl, [
                    RequestOptions::HEADERS => [
                        'Accept' => 'application/vnd.github.v3+json',
                        'Authorization' => sprintf('Token %s', $accessToken),
                        'Content-Type' => 'application/json',
                    ],
                    RequestOptions::BODY => Json::encode($body = [
                        'force' => true,
                        'sha' => $commitSha,
                    ]),
                ]);
            }
        }

        // TODO: What if pull request already exists? We will find out :-)
        // 5. Create pull request
        $pullRequestResponse = $client->request('POST', "https://api.github.com/repos/$repositoryName/pulls", [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $accessToken),
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY => Json::encode([
                'title' => 'Rector - Fix',
                'head' => $newBranch,
                'base' => $originalBranch,
                'body' => 'Automated pull request by Rector',
            ]),
        ]);
        $pullRequestResponseData = Json::decode($pullRequestResponse->getBody()->getContents());

        // TODO: update check -> passed or failed? failed if there were any changes

        return new Response($pullRequestResponseData->url);
    }
}
