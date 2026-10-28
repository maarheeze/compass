<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Events;

use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Models\Site;

readonly class SiteInspected
{
    use SerializesModels;

    public function __construct(
        public Site $site,
    ) {
    }
}
