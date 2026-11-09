<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Illuminate\Cache\ArrayStore;
use Illuminate\Contracts\Cache\Lock;

class RecordingLockStore extends ArrayStore
{
    /** @var list<int> */
    public array $lockedSeconds = [];

    /**
     * @param string $name
     * @param int $seconds
     * @param string|null $owner
     */
    public function lock($name, $seconds = 0, $owner = null): Lock
    {
        $this->lockedSeconds[] = $seconds;

        return parent::lock($name, $seconds, $owner);
    }
}
