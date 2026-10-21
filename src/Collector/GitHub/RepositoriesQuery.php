<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Webmozart\Assert\Assert;

readonly class RepositoriesQuery
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     */
    public function page(?string $cursor): RepositoriesQueryResult
    {
        $response = $this->gitHubClient->query($this->getQuery(), [
            'organization' => $this->organization,
            'cursor' => $cursor,
        ]);
        $repositoriesResponse = Assert::isArray($response->json('data.organization.repositories'));

        return new RepositoriesQueryResult(
            $this->buildGitHubRepositoriesCollection(Assert::isList($repositoriesResponse['nodes'])),
            $this->getNextCursor(Assert::isArray($repositoriesResponse['pageInfo'])),
        );
    }

    /**
     * @param list<mixed> $repositoryResponses
     */
    private function buildGitHubRepositoriesCollection(array $repositoryResponses): GitHubRepositoryCollection
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
                Assert::string($repositoryResponse['id']),
                Assert::string($repositoryResponse['name']),
                Assert::string($repositoryResponse['url']),
                Assert::string($defaultBranchResponse['name']),
                Assert::string($lastCommitResponse['oid']),
                RepositoryVisibility::from(Assert::string($repositoryResponse['visibility'])),
                $this->getOid($repositoryResponse['composerJson']),
                $this->getOid($repositoryResponse['composerLock']),
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

    private function getOid(mixed $objectResponse): ?string
    {
        if ($objectResponse === null) {
            return null;
        }

        return Assert::string(Assert::isArray($objectResponse)['oid']);
    }

    private function getQuery(): string
    {
        return <<<'GRAPHQL'
            query ($organization: String!, $cursor: String) {
              organization(login: $organization) {
                repositories(first: 100, after: $cursor, isArchived: false) {
                  pageInfo { hasNextPage endCursor }
                  nodes {
                    id
                    name
                    url
                    visibility
                    defaultBranchRef { name target { oid } }
                    composerJson: object(expression: "HEAD:composer.json") { oid }
                    composerLock: object(expression: "HEAD:composer.lock") { oid }
                  }
                }
              }
            }
            GRAPHQL;
    }
}
