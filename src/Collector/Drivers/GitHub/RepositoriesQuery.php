<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Carbon\CarbonImmutable;
use Generator;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\Repositories\CollectedRepository;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use UnexpectedValueException;
use Webmozart\Assert\Assert;

readonly class RepositoriesQuery
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @return Generator<int, RepositoriesQueryResult>
     *
     * @throws GitHubQueryFailed
     */
    public function pages(): Generator
    {
        $repositoriesQueryResult = $this->getRepositoriesQueryResult(null);

        yield $repositoriesQueryResult;

        while ($repositoriesQueryResult->nextCursor !== null) {
            $repositoriesQueryResult = $this->getRepositoriesQueryResult($repositoriesQueryResult->nextCursor);

            yield $repositoriesQueryResult;
        }
    }

    /**
     * @param list<mixed> $repositoryResponses
     */
    private function getCollectedRepositories(array $repositoryResponses): CollectedRepositoryCollection
    {
        $collectedRepositories = new CollectedRepositoryCollection();

        foreach ($repositoryResponses as $repositoryResponse) {
            Assert::isArray($repositoryResponse);

            if ($repositoryResponse['defaultBranchRef'] === null) {
                continue;
            }

            $defaultBranchResponse = Assert::isArray($repositoryResponse['defaultBranchRef']);
            $lastCommitResponse = Assert::isArray($defaultBranchResponse['target']);

            $collectedRepository = new CollectedRepository(
                Assert::string($repositoryResponse['id']),
                Assert::string($repositoryResponse['name']),
                Assert::string($repositoryResponse['nameWithOwner']),
                Assert::string($repositoryResponse['url']),
                Assert::string($defaultBranchResponse['name']),
                Assert::string($lastCommitResponse['oid']),
                CarbonImmutable::parse(Assert::string($lastCommitResponse['committedDate'])),
                $this->getVisibility(Assert::string($repositoryResponse['visibility'])),
                $this->getOid($repositoryResponse['composerJson']),
                $this->getOid($repositoryResponse['composerLock']),
                $this->getOid($repositoryResponse['packageJson']),
                $this->getOid($repositoryResponse['dependabotYml']),
            );

            $collectedRepositories->push($collectedRepository);
        }

        return $collectedRepositories;
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
                    nameWithOwner
                    url
                    visibility
                    defaultBranchRef { name target { oid ... on Commit { committedDate } } }
                    composerJson: object(expression: "HEAD:composer.json") { oid }
                    composerLock: object(expression: "HEAD:composer.lock") { oid }
                    packageJson: object(expression: "HEAD:package.json") { oid }
                    dependabotYml: object(expression: "HEAD:.github/dependabot.yml") { oid }
                  }
                }
              }
            }
            GRAPHQL;
    }

    /**
     * @throws GitHubQueryFailed
     */
    private function getRepositoriesQueryResult(?string $cursor): RepositoriesQueryResult
    {
        $response = $this->gitHubClient->query($this->getQuery(), [
            'organization' => $this->organization,
            'cursor' => $cursor,
        ]);
        $repositoriesResponse = Assert::isArray($response->json('data.organization.repositories'));

        return new RepositoriesQueryResult(
            $this->getCollectedRepositories(Assert::isList($repositoriesResponse['nodes'])),
            $this->getNextCursor(Assert::isArray($repositoriesResponse['pageInfo'])),
        );
    }

    private function getVisibility(string $visibilityResponse): RepositoryVisibility
    {
        return match ($visibilityResponse) {
            'INTERNAL' => RepositoryVisibility::INTERNAL,
            'PRIVATE' => RepositoryVisibility::PRIVATE,
            'PUBLIC' => RepositoryVisibility::PUBLIC,
            default => throw new UnexpectedValueException('unknown repository visibility'),
        };
    }
}
