<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Illuminate\Contracts\Bus\Dispatcher;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;

readonly class RepositoryCollector
{
    public function __construct(
        private Dispatcher $dispatcher,
    ) {
    }

    public function collect(CollectionRunId $collectionRunId): void
    {
        $this->dispatcher->dispatch(new CollectRepositories($collectionRunId, null));
    }
}
