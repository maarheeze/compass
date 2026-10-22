<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Webmozart\Assert\Assert;

readonly class GitHubClient
{
    public function __construct(
        private HttpFactory $http,
        private string $token,
    ) {
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
            throw new GitHubQueryFailed([], $exception);
        }

        if ($response->json('errors') !== null) {
            throw new GitHubQueryFailed($this->getErrorMessages($response), null);
        }

        return $response;
    }

    /**
     * @return list<string>
     */
    private function getErrorMessages(Response $response): array
    {
        $errorMessages = [];

        foreach (Assert::isList($response->json('errors')) as $errorResponse) {
            $errorMessages[] = Assert::string(Assert::isArray($errorResponse)['message']);
        }

        return $errorMessages;
    }
}
