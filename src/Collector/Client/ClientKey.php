<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Maarheeze\Compass\Models\Site;

readonly class ClientKey
{
    public function __construct(
        private string $defaultKey,
    ) {
    }

    public function forSite(Site $site): string
    {
        if ($site->client_key === null || $site->client_key === '') {
            return $this->defaultKey;
        }

        return $site->client_key;
    }
}
