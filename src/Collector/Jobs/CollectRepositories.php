<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHubClient;
use Maarheeze\Compass\Collector\GitHubRepository;
use Maarheeze\Compass\Collector\LinkHeader;
use Maarheeze\Compass\Collector\RepositorySynchronizer;
use Webmozart\Assert\Assert;

class CollectRepositories implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $url,
        private readonly CollectionRunId $collectionRunId,
    ) {
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle(
        GitHubClient $gitHubClient,
        RepositorySynchronizer $repositorySynchronizer,
        Dispatcher $dispatcher,
    ): void {
        $response = $gitHubClient->request()->get($this->url);
        $response->throw();
        $repositoryResponses = Assert::isList($response->json());

        $repositorySynchronizer->storeRepositories(
            $this->getGitHubRepositories($repositoryResponses),
            $this->collectionRunId,
        );

        $nextPageUrl = new LinkHeader($response)->nextPageUrl();

        if ($nextPageUrl !== null) {
            $dispatcher->dispatch(new self($nextPageUrl, $this->collectionRunId));

            return;
        }

        $repositorySynchronizer->updateInactive($this->collectionRunId);
    }

    /**
     * @param list<mixed> $repositoryResponses
     */
    private function getGitHubRepositories(array $repositoryResponses): GitHubRepositoryCollection
    {
        $gitHubRepositories = new GitHubRepositoryCollection();

        foreach ($repositoryResponses as $repositoryResponse) {
            Assert::isArray($repositoryResponse);

            if ($repositoryResponse['archived'] === true) {
                continue;
            }

            $gitHubRepositories->push(new GitHubRepository(
                Assert::string($repositoryResponse['name']),
                Assert::string($repositoryResponse['default_branch']),
            ));
        }

        return $gitHubRepositories;
    }
}
