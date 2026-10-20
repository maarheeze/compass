<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Contracts\Bus\Dispatcher;
use Maarheeze\Compass\Collector\Jobs\CollectRepositories;

use function sprintf;

readonly class RepositoryCollector
{
    private const int PER_PAGE = 100;

    public function __construct(
        private Dispatcher $dispatcher,
        private string $organization,
    ) {
    }

    public function collect(CollectionRunId $collectionRunId): void
    {
        $command = new CollectRepositories(
            sprintf('orgs/%s/repos?per_page=%d', $this->organization, self::PER_PAGE),
            $collectionRunId,
        );

        $this->dispatcher->dispatch($command);
    }
}
