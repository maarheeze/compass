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

/**
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property string $path
 * @property int $site_id
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

    protected static string $collectionClass = SiteEndpointCheckCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
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
        $siteEndpointCheckQuery->where(static function (Builder $statusCodeQuery): void {
            $statusCodeQuery
                ->whereNull('status_code')
                ->orWhereNotBetween('status_code', [200, 299]);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'status_code' => 'integer',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteEndpointCheckFactory
    {
        return SiteEndpointCheckFactory::new();
    }
}
