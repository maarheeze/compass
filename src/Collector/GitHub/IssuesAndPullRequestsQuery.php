<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Generator;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\Issues\CollectedIssue;
use Maarheeze\Compass\Collector\PullRequests\CollectedPullRequest;
use Webmozart\Assert\Assert;

use function array_key_last;
use function array_slice;
use function count;
use function explode;
use function http_build_query;
use function implode;
use function in_array;
use function sprintf;

readonly class IssuesAndPullRequestsQuery
{
    private const int PAGE_SIZE = 100;

    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @return Generator<int, CollectedIssueCollection>
     *
     * @throws GitHubQueryFailed
     */
    public function issues(): Generator
    {
        foreach ($this->searchPages('is:issue') as $itemResponses) {
            $collectedIssues = new CollectedIssueCollection();

            foreach ($itemResponses as $itemResponse) {
                $collectedIssues->push($this->getCollectedIssue($itemResponse));
            }

            yield $collectedIssues;
        }
    }

    /**
     * @return Generator<int, CollectedPullRequestCollection>
     *
     * @throws GitHubQueryFailed
     */
    public function pullRequests(): Generator
    {
        foreach ($this->searchPages('is:pr') as $itemResponses) {
            $collectedPullRequests = new CollectedPullRequestCollection();

            foreach ($itemResponses as $itemResponse) {
                $collectedPullRequests->push($this->getCollectedPullRequest($itemResponse));
            }

            yield $collectedPullRequests;
        }
    }

    /**
     * @param array<mixed> $itemResponse
     *
     * @return list<string>
     */
    private function getAssigneeLogins(array $itemResponse): array
    {
        $assigneeLogins = [];

        foreach (Assert::isList($itemResponse['assignees']) as $assigneeResponse) {
            $assigneeLogins[] = Assert::string(Assert::isArray($assigneeResponse)['login']);
        }

        return $assigneeLogins;
    }

    private function getAuthorLogin(mixed $userResponse): ?string
    {
        if ($userResponse === null) {
            return null;
        }

        return Assert::string(Assert::isArray($userResponse)['login']);
    }

    /**
     * @param array<mixed> $itemResponse
     */
    private function getCollectedIssue(array $itemResponse): CollectedIssue
    {
        return new CollectedIssue(
            Assert::string($itemResponse['node_id']),
            $this->getRepositoryFullName($itemResponse),
            Assert::integer($itemResponse['number']),
            Assert::string($itemResponse['title']),
            Assert::string($itemResponse['html_url']),
            $this->getAuthorLogin($itemResponse['user']),
            CarbonImmutable::parse(Assert::string($itemResponse['created_at'])),
            $this->getAssigneeLogins($itemResponse),
        );
    }

    /**
     * @param array<mixed> $itemResponse
     */
    private function getCollectedPullRequest(array $itemResponse): CollectedPullRequest
    {
        return new CollectedPullRequest(
            Assert::string($itemResponse['node_id']),
            $this->getRepositoryFullName($itemResponse),
            Assert::integer($itemResponse['number']),
            Assert::string($itemResponse['title']),
            Assert::string($itemResponse['html_url']),
            $this->getAuthorLogin($itemResponse['user']),
            Assert::boolean($itemResponse['draft']),
            CarbonImmutable::parse(Assert::string($itemResponse['created_at'])),
            $this->getAssigneeLogins($itemResponse),
        );
    }

    /**
     * @param list<array<mixed>> $itemResponses
     *
     * @return list<string>
     */
    private function getItemIdsCreatedAt(array $itemResponses, string $createdAt): array
    {
        $itemIds = [];

        foreach ($itemResponses as $itemResponse) {
            if ($itemResponse['created_at'] === $createdAt) {
                $itemIds[] = Assert::string($itemResponse['node_id']);
            }
        }

        return $itemIds;
    }

    /**
     * @return list<array<mixed>>
     *
     * @throws GitHubQueryFailed
     */
    private function getItemResponses(string $typeQualifier, ?string $createdFrom): array
    {
        $response = $this->gitHubClient->get($this->getSearchUrl($typeQualifier, $createdFrom));
        $searchResponse = Assert::isArray($response->json());

        if (Assert::boolean($searchResponse['incomplete_results'])) {
            throw new GitHubQueryFailed([], null);
        }

        $itemResponses = [];

        foreach (Assert::isList($searchResponse['items']) as $itemResponse) {
            $itemResponses[] = Assert::isArray($itemResponse);
        }

        return $itemResponses;
    }

    /**
     * @param array<mixed> $itemResponse
     */
    private function getRepositoryFullName(array $itemResponse): string
    {
        $repositoryUrlSegments = explode('/', Assert::string($itemResponse['repository_url']));

        return implode('/', array_slice($repositoryUrlSegments, -2));
    }

    private function getSearchUrl(string $typeQualifier, ?string $createdFrom): string
    {
        $qualifiers = [sprintf('org:%s', $this->organization), $typeQualifier, 'is:open', 'archived:false'];

        if ($createdFrom !== null) {
            $qualifiers[] = sprintf('created:>=%s', $createdFrom);
        }

        return sprintf('search/issues?%s', http_build_query([
            'q' => implode(' ', $qualifiers),
            'sort' => 'created',
            'order' => 'asc',
            'per_page' => self::PAGE_SIZE,
        ]));
    }

    /**
     * @param list<array<mixed>> $itemResponses
     * @param list<string> $seenItemIds
     *
     * @return list<array<mixed>>
     */
    private function getUnseenItemResponses(array $itemResponses, array $seenItemIds): array
    {
        $unseenItemResponses = [];

        foreach ($itemResponses as $itemResponse) {
            if (!in_array($itemResponse['node_id'], $seenItemIds, true)) {
                $unseenItemResponses[] = $itemResponse;
            }
        }

        return $unseenItemResponses;
    }

    /**
     * @return Generator<int, list<array<mixed>>>
     *
     * @throws GitHubQueryFailed
     */
    private function searchPages(string $typeQualifier): Generator
    {
        $createdFrom = null;
        $itemIdsSeenAtCreatedFrom = [];

        do {
            $itemResponses = $this->getItemResponses($typeQualifier, $createdFrom);

            yield $this->getUnseenItemResponses($itemResponses, $itemIdsSeenAtCreatedFrom);

            if (count($itemResponses) < self::PAGE_SIZE) {
                return;
            }

            $lastCreatedAt = Assert::string($itemResponses[array_key_last($itemResponses)]['created_at']);
            $itemIdsCreatedAtLast = $this->getItemIdsCreatedAt($itemResponses, $lastCreatedAt);
            $itemIdsSeenAtCreatedFrom = $lastCreatedAt === $createdFrom
                ? [...$itemIdsSeenAtCreatedFrom, ...$itemIdsCreatedAtLast]
                : $itemIdsCreatedAtLast;
            $createdFrom = $lastCreatedAt;
        } while (true);
    }
}
