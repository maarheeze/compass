<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Maarheeze\Uuid\IsUuid;
use Maarheeze\Uuid\UuidInterface;

final readonly class RunId implements UuidInterface
{
    use IsUuid;
}
