<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collections\SiteEndpointCollection;
use Maarheeze\Compass\Database\Factories\SiteEndpointFactory;

/**
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property string $path
 * @property int $site_id
 * @property CarbonImmutable $updated_at
 *
 * @property-read Site $site
 */
class SiteEndpoint extends Model
{
    /** @use HasFactory<SiteEndpointFactory> */
    use HasFactory;

    protected static string $collectionClass = SiteEndpointCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'path',
            'site_id',
        ];
    }

    public function getTable(): string
    {
        return 'compass_site_endpoints';
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
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteEndpointFactory
    {
        return SiteEndpointFactory::new();
    }
}
