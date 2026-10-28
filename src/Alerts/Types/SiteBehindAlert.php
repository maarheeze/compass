<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Alerts\AlertTypeDefinition;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class SiteBehindAlert implements AlertTypeDefinition
{
    public function __construct(
        private Translator $translator,
    ) {
    }

    public function description(Alert $alert): string
    {
        $site = $alert->subject;
        Assert::isInstanceOf($site, Site::class);

        return Assert::string($this->translator->get('compass::alert-description.SITE_BEHIND', [
            'site' => $site->name,
        ]));
    }
}
