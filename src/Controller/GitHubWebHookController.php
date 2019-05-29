<?php declare(strict_types=1);

namespace Rector\RectorCI\Controller;

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
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

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
            return new Response('Not reacting to commits by bots', Response::HTTP_ACCEPTED);
        }

        $originalBranch = $webhookData->check_suite->head_branch;
        $newBranch = $originalBranch . '-rectified';

        $privateKey = file_get_contents(__DIR__ . '/../../config/keys/rector-ci.pem');
        $token = [
            'iss' => getenv('GITHUB_APP_ID'),
            'exp' => time() + (10 * 60),
            'iat' => time(),
        ];

        $installationId = $webhookData->installation->id;
        $repositoryName = $webhookData->repository->full_name;

        $jwt = JWT::encode($token, $privateKey, 'RS256');

        $accessTokenResponse = $this->client->request(
            'POST',
            "https://api.github.com/app/installations/${installationId}/access_tokens",
            [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/vnd.github.machine-man-preview+json',
                    'Authorization' => sprintf('Bearer %s', $jwt),
                ],
            ]
        );

        $accessTokenResponseData = Json::decode($accessTokenResponse->getBody()->getContents());
        $accessToken = $accessTokenResponseData->token;

        // TODO: Create github check

        $cloneUrl = sprintf('https://x-access-token:%s@', $accessToken) . Strings::after(
            $webhookData->repository->clone_url,
            'https://'
        );
        $repositoryDirectory = __DIR__ . '/../../repositories/' . $repositoryName;

        if (! file_exists("../repositories/${repositoryName}")) {
            $cloneProcess = new Process(['git', 'clone', $cloneUrl, $repositoryDirectory]);
            $cloneProcess->mustRun();
        }

        $gitCheckoutChangesProcess = new Process(['git', 'checkout', '-f'], $repositoryDirectory);
        $gitCheckoutChangesProcess->mustRun();

        $gitFetchProcess = new Process(['git', 'fetch', '-p'], $repositoryDirectory);
        $gitFetchProcess->mustRun();

        $gitCheckoutHeadProcess = new Process([
            'git',
            'checkout',
            sprintf('origin/%s', $originalBranch),
        ], $repositoryDirectory);
        $gitCheckoutHeadProcess->mustRun();

        $composerInstallProcess = new Process(['composer', 'install'], $repositoryDirectory);
        $composerInstallProcess->mustRun();

        // @TODO rector binary
        // @TODO: determine what directories to search, recursive search for common used code directories? (src, packages/**/src, tests), or create .rector-ci.yaml?
        $rectorProcess = new Process([
            '../../../vendor/bin/rector',
            'process',
            'src',
            '--output-format=json',
        ], $repositoryDirectory, [
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
            $blobResponse = $this->client->request('POST', $blobUrl, [
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

        $treeResponse = $this->client->request('POST', $treeUrl, [
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
        $commitResponse = $this->client->request('POST', $commitUrl, [
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

        // 4. Create reference
        $referenceUrl = str_replace('{/sha}', '', $webhookData->repository->git_refs_url);

        // TODO: Instead of first trying it would be better to search for it and if not found create it
        try {
            $this->client->request('POST', $referenceUrl, [
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
        } catch (ClientException $clientException) {
            // Update reference, because it already exists
            if ($clientException->getCode() === 422) {
                $referenceUrl = str_replace('{/sha}', '/heads/' . $newBranch, $webhookData->repository->git_refs_url);

                $this->client->request('PATCH', $referenceUrl, [
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

        // TODO: What about taking pull requests info from checksuite hook?

        // Check if PR exists
        $pullsUrl = str_replace('{/number}', '', $webhookData->repository->pulls_url);
        $existingPullRequestResponse = $this->client->request('GET', "${pullsUrl}?head=${newBranch}", [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => sprintf('Token %s', $accessToken),
                'Content-Type' => 'application/json',
            ],
        ]);
        $existingPullRequestResponseData = Json::decode($existingPullRequestResponse->getBody()->getContents());

        // PR does not exist yet
        // 5. Create pull request, it does not exist
        // @TODO: this condition is wrong, probably search does not work properly
        // if (count($existingPullRequestResponseData) === 0) {
        $pullRequestResponse = $this->client->request('POST', $pullsUrl, [
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
        // }

        // TODO: What if pull request already exists? We will find out :-)
        // @TODO search for GET /repos/:owner/:repo/pulls?head=$newBranch

        // TODO: update check -> passed or failed? failed if there were any changes

        return new Response('OK');
    }
}
