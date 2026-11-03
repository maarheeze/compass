<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Exception;
use Throwable;

class CollectingFailed extends Exception
{
    public static function fromThrowable(Throwable $previous): self
    {
        return new self('Collecting failed.', 0, $previous);
    }
}
