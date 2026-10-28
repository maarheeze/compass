<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Alerts\AlertTypeDefinition;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;
use Webmozart\Assert\Assert;

readonly class DownSiteEndpointAlert implements AlertTypeDefinition
{
    public function __construct(
        private Translator $translator,
    ) {
    }

    public function description(Alert $alert): string
    {
        $siteEndpoint = $alert->subject;
        Assert::isInstanceOf($siteEndpoint, SiteEndpoint::class);

        return Assert::string($this->translator->get('compass::alert-description.DOWN_SITE_ENDPOINT', [
            'path' => $siteEndpoint->path,
            'site' => $siteEndpoint->site->name,
        ]));
    }
}
