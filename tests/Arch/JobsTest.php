<?php

declare(strict_types=1);

use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;

$batchedJobNamespaces = [
    'Maarheeze\Compass\Collector\Client\Jobs',
    'Maarheeze\Compass\Collector\Composer\Jobs',
    'Maarheeze\Compass\Collector\Dependabot\Jobs',
    'Maarheeze\Compass\Collector\Issues\Jobs',
    'Maarheeze\Compass\Collector\PullRequests\Jobs',
    'Maarheeze\Compass\Collector\Repositories\Jobs',
    'Maarheeze\Compass\Collector\SiteEndpoint\Jobs',
    'Maarheeze\Compass\Collector\Sites\Jobs',
];

arch('batched jobs interact with the queue, so their batch records them as done')
    ->expect($batchedJobNamespaces)
    ->toUseTraits([Batchable::class, InteractsWithQueue::class]);
