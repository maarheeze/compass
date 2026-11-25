<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

use Generator;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

interface RepositoriesCollector
{
    /**
     * @return Generator<int, CollectedRepositoryCollection>
     *
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function repositories(): Generator;
}
