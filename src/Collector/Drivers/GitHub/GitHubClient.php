<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Webmozart\Assert\Assert;

use function array_reverse;

readonly class GitHubClient
{
    public function __construct(
        private HttpFactory $http,
        private string $token,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     */
    public function get(string $url): Response
    {
        try {
            $response = $this->http
                ->baseUrl('https://api.github.com')
                ->withToken($this->token)
                ->acceptJson()
                ->get($url);
            $response->throw();
        } catch (ConnectionException | RequestException $exception) {
            throw new GitHubQueryFailed($exception);
        }

        return $response;
    }

    /**
     * @param array<string, string|null> $variables
     *
     * @throws GitHubQueryFailed
     */
    public function query(string $query, array $variables): Response
    {
        try {
            $response = $this->http
                ->baseUrl('https://api.github.com')
                ->withToken($this->token)
                ->acceptJson()
                ->post('graphql', [
                    'query' => $query,
                    'variables' => $variables,
                ]);
            $response->throw();
        } catch (ConnectionException | RequestException $exception) {
            throw new GitHubQueryFailed($exception);
        }

        if ($response->json('errors') !== null) {
            throw new GitHubQueryFailed($this->getGitHubAnsweredWithError($response));
        }

        return $response;
    }

    private function getGitHubAnsweredWithError(Response $response): ?GitHubAnsweredWithError
    {
        $gitHubAnsweredWithError = null;

        foreach (array_reverse(Assert::isList($response->json('errors'))) as $errorResponse) {
            $gitHubAnsweredWithError = new GitHubAnsweredWithError(
                Assert::string(Assert::isArray($errorResponse)['message']),
                $gitHubAnsweredWithError,
            );
        }

        return $gitHubAnsweredWithError;
    }
}
