<?php

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

require_once __DIR__ . '/vendor/autoload.php';

[, $originalBranch, $newBranch] = $argv;

$body = [
	'title' => 'Rector - Fix of branch ' . $originalBranch,
	'head' => $newBranch,
	'base' => $originalBranch,
];

$client = new Client();
$client->request('POST', 'https://api.github.com/repos/rectorphp/automatic-pull-request-test/pulls', [
	RequestOptions::HEADERS => [
		'Accept' => 'application/vnd.github.v3+json',
		'Authorization' => sprintf('Token %s', $_ENV['GITHUB_TOKEN']),
		'Content-Type' => 'application/json',
	],
	RequestOptions::BODY => json_encode($body),
]);
