<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;

/**
 * @extends Builder<AlertCheck>
 */
class AlertCheckBuilder extends Builder
{
    public function applicable(): self
    {
        return $this->whereNot('condition', ConditionState::NOT_APPLICABLE);
    }

    public function ofSubject(AlertSubject&Model $alertSubject): self
    {
        return $this->whereMorphedTo('subject', $alertSubject);
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
