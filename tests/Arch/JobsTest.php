<?php

declare(strict_types=1);

use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\LockedJob;

arch('batched jobs interact with the queue, so their batch records them as done')
    ->expect('Maarheeze\Compass\Collector\*\Jobs')
    ->toUseTraits([Batchable::class, InteractsWithQueue::class]);

arch('collector and task jobs say what they lock on, so whatever queues them can protect them')
    ->expect('Maarheeze\Compass\Collector\*\Jobs')
    ->toImplement(LockedJob::class);

arch('collector and task jobs keep retrying while their lock is held instead of failing on the first release')
    ->expect('Maarheeze\Compass\Collector\*\Jobs')
    ->toUseTrait(RetriesWhileLocked::class);
