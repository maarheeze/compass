<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Illuminate\Http\Client\Factory as HttpFactory;

readonly class GitHubClientFactory
{
    public function __construct(
        private HttpFactory $http,
    ) {
    }

    public function make(string $token): GitHubClient
    {
        return new GitHubClient($this->http, $token);
    }
}
