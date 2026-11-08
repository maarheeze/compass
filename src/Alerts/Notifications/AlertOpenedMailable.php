<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Notifications;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Mail\Mailable;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Models\Alert;
use Webmozart\Assert\Assert;

class AlertOpenedMailable extends Mailable
{
    public function __construct(
        public readonly Alert $alert,
        public readonly AlertType $alertType,
    ) {
    }

    public function build(Translator $translator): void
    {
        $alertTypeLabel = Assert::string($translator->get($this->alertType->translationKey()));
        $subject = Assert::string($translator->get('compass::notification.alert_opened_subject', [
            'type' => $alertTypeLabel,
            'subject' => $this->alert->subject->displayName(),
        ]));

        $this
            ->subject($subject)
            ->text('compass::mail.alert-opened', ['alertTypeLabel' => $alertTypeLabel]);
    }
}
