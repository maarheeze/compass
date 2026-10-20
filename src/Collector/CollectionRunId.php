<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Maarheeze\Uuid\IsUuid;
use Maarheeze\Uuid\UuidInterface;

final readonly class CollectionRunId implements UuidInterface
{
    use IsUuid;
}
