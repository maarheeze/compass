<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collections\SiteScheduledTaskCollection;
use Maarheeze\Compass\Database\Factories\SiteScheduledTaskFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property string $command
 * @property CarbonImmutable $created_at
 * @property string $expression
 * @property Uuid $id
 * @property Uuid $site_report_id
 * @property CarbonImmutable $updated_at
 *
 * @property-read SiteReport $siteReport
 */
class SiteScheduledTask extends Model
{
    /** @use HasCollection<SiteScheduledTaskCollection> */
    use HasCollection;
    /** @use HasFactory<SiteScheduledTaskFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = SiteScheduledTaskCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'command',
            'expression',
            'site_report_id',
        ];
    }

    public function getTable(): string
    {
        return 'compass_site_scheduled_tasks';
    }

    /**
     * @return BelongsTo<SiteReport, $this>
     */
    public function siteReport(): BelongsTo
    {
        return $this->belongsTo(SiteReport::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'site_report_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteScheduledTaskFactory
    {
        return SiteScheduledTaskFactory::new();
    }
}
