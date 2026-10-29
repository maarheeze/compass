<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Notifications;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Mail\Mailable;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\EnumLabels;
use Webmozart\Assert\Assert;

class AlertOpenedMailable extends Mailable
{
    public function __construct(
        public readonly Alert $alert,
    ) {
    }

    public function build(EnumLabels $enumLabels, Translator $translator): void
    {
        $alertTypeLabel = Assert::string($translator->get($enumLabels->labelKey($this->alert->type)));
        $subject = Assert::string($translator->get('compass::notification.alert_opened_subject', [
            'type' => $alertTypeLabel,
            'subject' => $this->alert->subject->displayName(),
        ]));

        $this
            ->subject($subject)
            ->text('compass::mail.alert-opened', ['alertTypeLabel' => $alertTypeLabel]);
    }
}
