<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Webmozart\Assert\Assert;

readonly class RepositoriesQuery
{
    private const string QUERY = <<<'GRAPHQL'
        query ($organization: String!, $cursor: String) {
          organization(login: $organization) {
            repositories(first: 100, after: $cursor, isArchived: false) {
              pageInfo { hasNextPage endCursor }
              nodes {
                name
                visibility
                defaultBranchRef { name target { oid } }
              }
            }
          }
        }
        GRAPHQL;

    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @throws ConnectionException
     * @throws GitHubQueryFailed
     * @throws RequestException
     */
    public function page(?string $cursor): RepositoriesQueryResult
    {
        $response = $this->gitHubClient
            ->request()
            ->post('graphql', [
                'query' => self::QUERY,
                'variables' => [
                    'organization' => $this->organization,
                    'cursor' => $cursor,
                ],
            ]);
        $response->throw();

        if ($response->json('errors') !== null) {
            throw new GitHubQueryFailed();
        }

        $repositoriesResponse = Assert::isArray($response->json('data.organization.repositories'));

        return new RepositoriesQueryResult(
            $this->getGitHubRepositories(Assert::isList($repositoriesResponse['nodes'])),
            $this->getNextCursor(Assert::isArray($repositoriesResponse['pageInfo'])),
        );
    }

    /**
     * @param list<mixed> $repositoryResponses
     */
    private function getGitHubRepositories(array $repositoryResponses): GitHubRepositoryCollection
    {
        $gitHubRepositories = new GitHubRepositoryCollection();

        foreach ($repositoryResponses as $repositoryResponse) {
            Assert::isArray($repositoryResponse);

            if ($repositoryResponse['defaultBranchRef'] === null) {
                continue;
            }

            $defaultBranchResponse = Assert::isArray($repositoryResponse['defaultBranchRef']);
            $lastCommitResponse = Assert::isArray($defaultBranchResponse['target']);

            $gitHubRepository = new GitHubRepository(
                Assert::string($repositoryResponse['name']),
                Assert::string($defaultBranchResponse['name']),
                Assert::string($lastCommitResponse['oid']),
                RepositoryVisibility::from(Assert::string($repositoryResponse['visibility'])),
            );

            $gitHubRepositories->push($gitHubRepository);
        }

        return $gitHubRepositories;
    }

    /**
     * @param array<mixed> $pageInfoResponse
     */
    private function getNextCursor(array $pageInfoResponse): ?string
    {
        if ($pageInfoResponse['hasNextPage'] !== true) {
            return null;
        }

        return Assert::string($pageInfoResponse['endCursor']);
    }
}
