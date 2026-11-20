<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collections\SiteDomainCollection;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainType;
use Maarheeze\Compass\Database\Factories\SiteDomainFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property string $name
 * @property Uuid $site_id
 * @property SiteDomainStatus $status
 * @property SiteDomainType $type
 * @property CarbonImmutable $updated_at
 *
 * @property-read Site $site
 */
class SiteDomain extends Model
{
    /** @use HasCollection<SiteDomainCollection> */
    use HasCollection;
    /** @use HasFactory<SiteDomainFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = SiteDomainCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'name',
            'site_id',
            'status',
            'type',
        ];
    }

    public function getTable(): string
    {
        return 'compass_site_domains';
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
            'site_id' => UuidCast::class,
            'status' => SiteDomainStatus::class,
            'type' => SiteDomainType::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteDomainFactory
    {
        return SiteDomainFactory::new();
    }
}
