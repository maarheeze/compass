<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;

readonly class GitHubClient
{
    public function __construct(
        private HttpFactory $http,
        private string $token,
    ) {
    }

    public function request(): PendingRequest
    {
        return $this->http
            ->baseUrl('https://api.github.com')
            ->withToken($this->token)
            ->acceptJson();
    }
}
