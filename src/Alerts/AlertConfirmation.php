<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Illuminate\Contracts\Session\Session;
use Webmozart\Assert\Assert;

readonly class AlertConfirmation
{
    private const string SESSION_KEY = 'compass.alert-confirmation';

    public function __construct(
        private Session $session,
    ) {
    }

    public function confirmedEntryType(): ?AlertHistoryEntryType
    {
        $confirmedEntryTypeValue = $this->session->get(self::SESSION_KEY);

        if ($confirmedEntryTypeValue === null) {
            return null;
        }

        return AlertHistoryEntryType::from(Assert::string($confirmedEntryTypeValue));
    }

    public function flash(AlertHistoryEntryType $confirmedEntryType): void
    {
        $this->session->flash(self::SESSION_KEY, $confirmedEntryType->value);
    }
}
