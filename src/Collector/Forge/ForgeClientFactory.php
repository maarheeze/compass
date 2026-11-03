<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Illuminate\Http\Client\Factory as HttpFactory;

readonly class ForgeClientFactory
{
    public function __construct(
        private HttpFactory $http,
    ) {
    }

    public function make(string $token): ForgeClient
    {
        return new ForgeClient($this->http, $token);
    }
}
