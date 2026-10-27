<?php

declare(strict_types=1);

use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;

$batchedJobNamespaces = [
    'Maarheeze\Compass\Collector\Client\Jobs',
    'Maarheeze\Compass\Collector\Forge\Jobs',
    'Maarheeze\Compass\Collector\GitHub\Jobs',
    'Maarheeze\Compass\Collector\SiteEndpoint\Jobs',
];

arch('batched jobs interact with the queue, so their batch records them as done')
    ->expect($batchedJobNamespaces)
    ->toUseTraits([Batchable::class, InteractsWithQueue::class]);
