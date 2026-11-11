<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collections\SiteEndpointCheckCollection;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Database\Factories\SiteEndpointCheckFactory;
use Maarheeze\Compass\Models\Builders\SiteEndpointCheckBuilder;
use Maarheeze\Compass\Models\Casts\EndpointPathCast;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property bool $is_successful
 * @property ?CarbonImmutable $online_since
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
    /** @use HasBuilder<SiteEndpointCheckBuilder> */
    use HasBuilder;
    /** @use HasCollection<SiteEndpointCheckCollection> */
    use HasCollection;
    /** @use HasFactory<SiteEndpointCheckFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $builder = SiteEndpointCheckBuilder::class;
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
