<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint;

readonly class EndpointPath
{
    public function __construct(
        private string $path,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->path;
    }
}
