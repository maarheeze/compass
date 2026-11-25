<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Webmozart\Assert\Assert;

use function array_reverse;

readonly class GitHubClient
{
    public function __construct(
        private HttpFactory $http,
        private string $token,
        private RequestBudget $requestBudget,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     * @throws RequestBudgetExhausted
     */
    public function get(string $url): Response
    {
        $this->requestBudget->reserveRequest();

        try {
            $response = $this->http
                ->baseUrl('https://api.github.com')
                ->withToken($this->token)
                ->acceptJson()
                ->get($url);
        } catch (ConnectionException $exception) {
            throw new GitHubQueryFailed($exception);
        }

        return $this->getSuccessfulResponse($response);
    }

    /**
     * @param array<string, string|null> $variables
     *
     * @throws GitHubQueryFailed
     * @throws RequestBudgetExhausted
     */
    public function query(string $query, array $variables): Response
    {
        $this->requestBudget->reserveRequest();

        try {
            $response = $this->http
                ->baseUrl('https://api.github.com')
                ->withToken($this->token)
                ->acceptJson()
                ->post('graphql', [
                    'query' => $query,
                    'variables' => $variables,
                ]);
        } catch (ConnectionException $exception) {
            throw new GitHubQueryFailed($exception);
        }

        $successfulResponse = $this->getSuccessfulResponse($response);

        if ($successfulResponse->json('errors') !== null) {
            throw new GitHubQueryFailed($this->getGitHubAnsweredWithError($successfulResponse));
        }

        return $successfulResponse;
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

    /**
     * @throws GitHubQueryFailed
     * @throws RequestBudgetExhausted
     */
    private function getSuccessfulResponse(Response $response): Response
    {
        if ($this->isRateLimited($response)) {
            throw RequestBudgetExhausted::fromRateLimitedResponse($response);
        }

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new GitHubQueryFailed($exception);
        }

        return $response;
    }

    private function isRateLimited(Response $response): bool
    {
        if ($response->tooManyRequests()) {
            return true;
        }

        if (!$response->forbidden()) {
            return false;
        }

        return $response->header('X-RateLimit-Remaining') === '0' || $response->hasHeader('Retry-After');
    }
}
