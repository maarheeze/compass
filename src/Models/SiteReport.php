<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteEndpointCollection;
use Maarheeze\Compass\Collections\SiteReportCollection;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Database\Factories\SiteReportFactory;
use Maarheeze\Compass\Models\Concerns\HasAlerts;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property ?bool $app_debug
 * @property ?string $app_env
 * @property ?string $app_name
 * @property CarbonImmutable $collected_at
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property ?bool $maintenance_mode
 * @property ?string $path
 * @property Uuid $site_id
 * @property string $source
 * @property SiteReportStatus $status
 * @property CarbonImmutable $updated_at
 *
 * @property-read AlertCheckCollection $alertChecks
 * @property-read AlertCollection $alerts
 * @property-read SiteEndpointCollection $endpoints
 * @property-read Site $site
 */
class SiteReport extends Model implements AlertSubject
{
    use HasAlerts;
    /** @use HasCollection<SiteReportCollection> */
    use HasCollection;
    /** @use HasFactory<SiteReportFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = SiteReportCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'app_debug',
            'app_env',
            'app_name',
            'collected_at',
            'maintenance_mode',
            'path',
            'site_id',
            'source',
            'status',
        ];
    }

    public function getTable(): string
    {
        return 'compass_site_reports';
    }

    /**
     * @return HasMany<SiteEndpoint, $this>
     */
    public function endpoints(): HasMany
    {
        return $this->hasMany(SiteEndpoint::class);
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'app_debug' => 'boolean',
            'collected_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'maintenance_mode' => 'boolean',
            'site_id' => UuidCast::class,
            'status' => SiteReportStatus::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteReportFactory
    {
        return SiteReportFactory::new();
    }
}
