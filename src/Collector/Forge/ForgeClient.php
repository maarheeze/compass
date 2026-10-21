<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

readonly class ForgeClient
{
    public function __construct(
        private HttpFactory $http,
        private string $token,
    ) {
    }

    /**
     * @param array<string, int|string|null> $query
     *
     * @throws ForgeRequestFailed
     */
    public function get(string $path, array $query): Response
    {
        try {
            $response = $this->http
                ->baseUrl('https://forge.laravel.com/api')
                ->withToken($this->token)
                ->acceptJson()
                ->get($path, $query);
            $response->throw();
        } catch (ConnectionException | RequestException $exception) {
            throw new ForgeRequestFailed(previous: $exception);
        }

        return $response;
    }
}
