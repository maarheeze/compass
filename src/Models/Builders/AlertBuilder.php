<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Contracts\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\User;

/**
 * @extends Builder<Alert>
 */
class AlertBuilder extends Builder
{
    public function assigned(): self
    {
        return $this->whereNotNull('assignee_id');
    }

    public function done(): self
    {
        return $this->where('status', AlertStatus::DONE);
    }

    public function muted(): self
    {
        return $this->where('is_muted', true);
    }

    public function notAssignedTo(User $user): self
    {
        return $this->where(static function (self $alertQuery) use ($user): void {
            $alertQuery
                ->whereNull('assignee_id')
                ->orWhereNot('assignee_id', $user->id);
        });
    }

    public function notDone(): self
    {
        return $this->whereNot('status', AlertStatus::DONE);
    }

    public function notMuted(): self
    {
        return $this->where('is_muted', false);
    }

    public function ofSameSubjectAs(Alert $alert): self
    {
        return $this
            ->where('subject_type', $alert->subject_type)
            ->where('subject_id', $alert->subject_id);
    }

    public function ofSameTypeAs(Alert $alert): self
    {
        return $this->where('alert_type_identifier', $alert->alert_type_identifier);
    }

    public function ofSite(Site $site): self
    {
        return $this->where(static function (self $alertQuery) use ($site): void {
            $alertQuery->ofSubject($site);
            $alertQuery->orWhereHasMorph(
                'subject',
                [SiteEndpoint::class],
                static function (QueryBuilder $siteEndpointQuery) use ($site): void {
                    $siteEndpointQuery->whereRelation('siteReport', 'site_id', $site->id);
                },
            );
            $alertQuery->orWhereHasMorph(
                'subject',
                [SiteReport::class],
                static function (QueryBuilder $siteReportQuery) use ($site): void {
                    $siteReportQuery->where('site_id', $site->id);
                },
            );
        });
    }

    public function ofSubject(AlertSubject&Model $alertSubject): self
    {
        return $this->whereMorphedTo('subject', $alertSubject);
    }

    public function ofType(AlertType $alertType): self
    {
        return $this->where('alert_type_identifier', $alertType->identifier());
    }

    public function withSubject(): self
    {
        return $this->with(['subject' => static function (MorphTo $subjectRelation): void {
            $subjectRelation->morphWith([
                SiteEndpoint::class => ['siteReport.site'],
                SiteReport::class => ['site'],
            ]);
        },
        ]);
    }
}
