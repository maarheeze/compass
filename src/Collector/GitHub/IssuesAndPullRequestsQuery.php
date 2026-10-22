<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Maarheeze\Compass\Collections\GitHubIssueCollection;
use Maarheeze\Compass\Collections\GitHubPullRequestCollection;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function http_build_query;
use function preg_match;
use function rawurlencode;
use function sprintf;

readonly class IssuesAndPullRequestsQuery
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     */
    public function page(?string $pageUrl): IssuesAndPullRequestsQueryResult
    {
        $response = $this->gitHubClient->get($this->getPageUrl($pageUrl));
        $gitHubIssues = new GitHubIssueCollection();
        $gitHubPullRequests = new GitHubPullRequestCollection();

        foreach (Assert::isList($response->json()) as $issueResponse) {
            Assert::isArray($issueResponse);

            if (array_key_exists('pull_request', $issueResponse)) {
                $gitHubPullRequests->push($this->getGitHubPullRequest($issueResponse));
            } else {
                $gitHubIssues->push($this->getGitHubIssue($issueResponse));
            }
        }

        return new IssuesAndPullRequestsQueryResult(
            $gitHubIssues,
            $gitHubPullRequests,
            $this->getNextPageUrl($response),
        );
    }

    /**
     * @param array<mixed> $issueResponse
     *
     * @return list<string>
     */
    private function getAssigneeLogins(array $issueResponse): array
    {
        $assigneeLogins = [];

        foreach (Assert::isList($issueResponse['assignees']) as $assigneeResponse) {
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
     * @param array<mixed> $issueResponse
     */
    private function getGitHubIssue(array $issueResponse): GitHubIssue
    {
        return new GitHubIssue(
            Assert::string($issueResponse['node_id']),
            $this->getRepositoryName($issueResponse),
            Assert::integer($issueResponse['number']),
            Assert::string($issueResponse['title']),
            Assert::string($issueResponse['html_url']),
            $this->getAuthorLogin($issueResponse['user']),
            CarbonImmutable::parse(Assert::string($issueResponse['created_at'])),
            $this->getAssigneeLogins($issueResponse),
        );
    }

    /**
     * @param array<mixed> $issueResponse
     */
    private function getGitHubPullRequest(array $issueResponse): GitHubPullRequest
    {
        return new GitHubPullRequest(
            Assert::string($issueResponse['node_id']),
            $this->getRepositoryName($issueResponse),
            Assert::integer($issueResponse['number']),
            Assert::string($issueResponse['title']),
            Assert::string($issueResponse['html_url']),
            $this->getAuthorLogin($issueResponse['user']),
            Assert::boolean($issueResponse['draft']),
            CarbonImmutable::parse(Assert::string($issueResponse['created_at'])),
            $this->getAssigneeLogins($issueResponse),
        );
    }

    private function getNextPageUrl(Response $response): ?string
    {
        if (preg_match('/<([^>]+)>;\s*rel="next"/', $response->header('Link'), $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function getPageUrl(?string $pageUrl): string
    {
        if ($pageUrl !== null) {
            return $pageUrl;
        }

        return sprintf(
            'orgs/%s/issues?%s',
            rawurlencode($this->organization),
            http_build_query([
                'filter' => 'all',
                'state' => 'open',
                'per_page' => 100,
            ]),
        );
    }

    /**
     * @param array<mixed> $issueResponse
     */
    private function getRepositoryName(array $issueResponse): string
    {
        return Assert::string(Assert::isArray($issueResponse['repository'])['name']);
    }
}
