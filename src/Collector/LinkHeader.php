<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Http\Client\Response;

use function preg_match;

readonly class LinkHeader
{
    public function __construct(
        private Response $response,
    ) {
    }

    public function nextPageUrl(): ?string
    {
        if (preg_match('/<([^>]+)>;\s*rel="next"/', $this->response->header('Link'), $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
