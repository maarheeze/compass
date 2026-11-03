<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

use Generator;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\CollectingFailed;

interface RepositoriesCollector
{
    /**
     * @return Generator<int, CollectedRepositoryCollection>
     *
     * @throws CollectingFailed
     */
    public function repositories(): Generator;
}
