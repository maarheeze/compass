<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Maarheeze\Compass\Models\Site;

use function sprintf;

readonly class ForgeSiteUrlFactory
{
    public function __construct(
        private string $organization,
    ) {
    }

    public function create(Site $site): string
    {
        return sprintf(
            'https://forge.laravel.com/%s/%s/%s',
            $this->organization,
            $site->server_slug,
            $site->forge_id,
        );
    }
}
