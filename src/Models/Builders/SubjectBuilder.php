<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Contracts\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

/**
 * @template TModel of Model
 *
 * @extends Builder<TModel>
 */
abstract class SubjectBuilder extends Builder
{
    public function ofSiteOrItsReports(Site $site): static
    {
        return $this->where(static function (self $subjectQuery) use ($site): void {
            $subjectQuery->ofSubject($site);
            $subjectQuery->orWhereHasMorph(
                'subject',
                [SiteReport::class],
                static function (QueryBuilder $siteReportQuery) use ($site): void {
                    $siteReportQuery->where('site_id', $site->id);
                },
            );
        });
    }

    public function ofSubject(AlertSubject&Model $alertSubject): static
    {
        return $this->whereMorphedTo('subject', $alertSubject);
    }
}
