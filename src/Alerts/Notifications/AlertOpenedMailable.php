<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Notifications;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Mail\Mailable;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertSubjectName;
use Webmozart\Assert\Assert;

use function html_entity_decode;

use const ENT_HTML5;
use const ENT_QUOTES;

class AlertOpenedMailable extends Mailable
{
    public function __construct(
        public readonly Alert $alert,
        public readonly AlertType $alertType,
    ) {
    }

    public function build(Translator $translator, ViewFactory $view): void
    {
        $alertTypeLabel = Assert::string($translator->get($this->alertType->translationKey()));
        $alertSubjectName = new AlertSubjectName($this->alert->subject, $view);
        $subjectName = html_entity_decode($alertSubjectName->render()->toHtml(), ENT_QUOTES | ENT_HTML5);
        $subject = Assert::string($translator->get('compass::notification.alert_opened_subject', [
            'type' => $alertTypeLabel,
            'subject' => $subjectName,
        ]));

        $this
            ->subject($subject)
            ->text('compass::mail.alert-opened', [
                'alertSubjectName' => $subjectName,
                'alertTypeLabel' => $alertTypeLabel,
            ]);
    }
}
