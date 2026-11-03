<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteEndpointCollection;
use Maarheeze\Compass\Database\Factories\SiteEndpointFactory;
use Maarheeze\Compass\Models\Builders\SiteEndpointBuilder;
use Maarheeze\Compass\Models\Concerns\HasAlerts;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

use function sprintf;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property string $path
 * @property Uuid $site_id
 * @property CarbonImmutable $updated_at
 *
 * @property-read AlertCollection $alerts
 * @property-read Site $site
 */
class SiteEndpoint extends Model implements AlertSubject
{
    use HasAlerts;
    /** @use HasBuilder<SiteEndpointBuilder> */
    use HasBuilder;
    /** @use HasCollection<SiteEndpointCollection> */
    use HasCollection;
    /** @use HasFactory<SiteEndpointFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $builder = SiteEndpointBuilder::class;
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

    public function displayName(): string
    {
        return sprintf('%s%s', $this->site->name, $this->path);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'site_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteEndpointFactory
    {
        return SiteEndpointFactory::new();
    }
}
