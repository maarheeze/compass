<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\Forge;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

readonly class ForgeClient
{
    public function __construct(
        private HttpFactory $http,
        private string $token,
        private RequestBudget $requestBudget,
    ) {
    }

    /**
     * @param array<string, int|string|null> $query
     *
     * @throws ForgeRequestFailed
     * @throws RequestBudgetExhausted
     */
    public function get(string $path, array $query): Response
    {
        $this->requestBudget->reserveRequest();

        try {
            $response = $this->http
                ->baseUrl('https://forge.laravel.com/api')
                ->withToken($this->token)
                ->acceptJson()
                ->get($path, $query);
        } catch (ConnectionException $exception) {
            throw new ForgeRequestFailed(previous: $exception);
        }

        if ($response->tooManyRequests()) {
            throw RequestBudgetExhausted::fromRateLimitedResponse($response);
        }

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new ForgeRequestFailed(previous: $exception);
        }

        return $response;
    }
}
