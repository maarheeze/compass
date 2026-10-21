<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Forge\ForgeRequestFailed;
use Maarheeze\Compass\Collector\Forge\SitesQuery;
use Maarheeze\Compass\Collector\Forge\SiteSynchronizer;

class CollectSites implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CollectionRunId $collectionRunId,
        private readonly ?string $cursor,
    ) {
    }

    /**
     * @throws ForgeRequestFailed
     */
    public function handle(
        SitesQuery $sitesQuery,
        SiteSynchronizer $siteSynchronizer,
    ): void {
        $sitesQueryResult = $sitesQuery->page($this->cursor);

        $siteSynchronizer->storeSites(
            $sitesQueryResult->forgeSites,
            $this->collectionRunId,
        );

        if ($sitesQueryResult->nextCursor !== null) {
            $this->prependToChain(new self($this->collectionRunId, $sitesQueryResult->nextCursor));

            return;
        }

        $siteSynchronizer->updateInactive($this->collectionRunId);
    }
}
