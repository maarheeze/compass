<?php

declare(strict_types=1);

use Maarheeze\Compass\Providers\CollectorProvider;

arch('only the collector provider builds the drivers')
    ->expect('Maarheeze\Compass\Collector\Drivers')
    ->toOnlyBeUsedIn([
        'Maarheeze\Compass\Collector\Drivers',
        CollectorProvider::class,
    ]);
