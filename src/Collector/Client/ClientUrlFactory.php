<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Maarheeze\Compass\Models\Site;

use function ltrim;
use function rtrim;
use function sprintf;

readonly class ClientUrlFactory
{
    public function __construct(
        private string $defaultPath,
    ) {
    }

    public function create(Site $site): string
    {
        return sprintf(
            '%s/%s',
            rtrim($site->url, '/'),
            ltrim($this->path($site), '/'),
        );
    }

    public function path(Site $site): string
    {
        if ($site->client_path === null) {
            return $this->defaultPath;
        }

        return $site->client_path;
    }
}
