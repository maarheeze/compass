<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Alerts\AlertTypeDefinition;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

readonly class MissingRepositoryAlert implements AlertTypeDefinition
{
    public function __construct(
        private Translator $translator,
    ) {
    }

    public function description(Alert $alert): string
    {
        $repository = $alert->subject;
        Assert::isInstanceOf($repository, Repository::class);

        return Assert::string($this->translator->get('compass::alert-description.MISSING_REPOSITORY', [
            'repository' => $repository->name,
        ]));
    }
}
