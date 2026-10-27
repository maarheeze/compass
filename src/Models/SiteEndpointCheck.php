<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collections\SiteEndpointCheckCollection;
use Maarheeze\Compass\Database\Factories\SiteEndpointCheckFactory;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Models\Casts\EndpointPathCast;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property bool $is_successful
 * @property EndpointPath $path
 * @property Uuid $site_id
 * @property ?int $status_code
 * @property CarbonImmutable $updated_at
 * @property string $url
 *
 * @property-read Site $site
 */
class SiteEndpointCheck extends Model
{
    /** @use HasFactory<SiteEndpointCheckFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = SiteEndpointCheckCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'is_successful',
            'path',
            'site_id',
            'status_code',
            'url',
        ];
    }

    public function getTable(): string
    {
        return 'compass_site_endpoint_checks';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @param Builder<self> $siteEndpointCheckQuery
     */
    public function scopeDown(Builder $siteEndpointCheckQuery): void
    {
        $siteEndpointCheckQuery->where('is_successful', false);
    }

    /**
     * @param Builder<self> $siteEndpointCheckQuery
     */
    public function scopeLatestPerPath(Builder $siteEndpointCheckQuery): void
    {
        $siteEndpointCheckQuery->whereNotExists(
            self::query()
                ->from('compass_site_endpoint_checks', 'newer_site_endpoint_checks')
                ->whereColumn('newer_site_endpoint_checks.site_id', 'compass_site_endpoint_checks.site_id')
                ->whereColumn('newer_site_endpoint_checks.path', 'compass_site_endpoint_checks.path')
                ->whereColumn('newer_site_endpoint_checks.id', '>', 'compass_site_endpoint_checks.id'),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'is_successful' => 'boolean',
            'path' => EndpointPathCast::class,
            'site_id' => UuidCast::class,
            'status_code' => 'integer',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteEndpointCheckFactory
    {
        return SiteEndpointCheckFactory::new();
    }
}
