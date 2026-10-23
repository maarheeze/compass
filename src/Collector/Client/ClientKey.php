<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class ClientKey
{
    public function __construct(
        private ?string $defaultKey,
    ) {
    }

    public function find(Site $site): ?string
    {
        if ($this->isSet($site->client_key)) {
            return $site->client_key;
        }

        if ($this->isSet($this->defaultKey)) {
            return $this->defaultKey;
        }

        return null;
    }

    public function forSite(Site $site): string
    {
        return Assert::string($this->find($site));
    }

    public function hasDefaultKey(): bool
    {
        return $this->isSet($this->defaultKey);
    }

    public function hasKey(Site $site): bool
    {
        return $this->find($site) !== null;
    }

    private function isSet(?string $key): bool
    {
        return $key !== null && $key !== '';
    }
}
