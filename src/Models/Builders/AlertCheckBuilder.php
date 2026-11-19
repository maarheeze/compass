<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Webmozart\Assert\Assert;

/**
 * @extends SubjectBuilder<AlertCheck>
 */
class AlertCheckBuilder extends SubjectBuilder
{
    public function applicable(): self
    {
        return $this->whereNot('condition', ConditionState::NOT_APPLICABLE);
    }

    public function listed(): AlertCheckCollection
    {
        return Assert::isInstanceOf(
            $this
                ->applicable()
                ->withAlertMuted()
                ->orderBy('alert_type_identifier')
                ->get(),
            AlertCheckCollection::class,
        );
    }

    public function withAlertMuted(): self
    {
        return $this
            ->addSelect(['alert_is_muted' => $this->openAlertOfCheck()->select('compass_alerts.is_muted')])
            ->withCasts(['alert_is_muted' => 'boolean']);
    }

    private function openAlertOfCheck(): AlertBuilder
    {
        return Alert::query()
            ->whereColumn('compass_alerts.subject_type', 'compass_alert_checks.subject_type')
            ->whereColumn('compass_alerts.subject_id', 'compass_alert_checks.subject_id')
            ->whereColumn('compass_alerts.alert_type_identifier', 'compass_alert_checks.alert_type_identifier')
            ->notDone()
            ->limit(1);
    }
}
