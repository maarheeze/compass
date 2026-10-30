<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Collections\DependabotYmlCollection;
use Maarheeze\Compass\Database\Factories\DependabotYmlFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property string $oid
 * @property Collection<int, string> $package_ecosystems
 * @property Uuid $repository_id
 * @property CarbonImmutable $updated_at
 *
 * @property-read Repository $repository
 */
class DependabotYml extends Model
{
    /** @use HasCollection<DependabotYmlCollection> */
    use HasCollection;
    /** @use HasFactory<DependabotYmlFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = DependabotYmlCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'oid',
            'package_ecosystems',
            'repository_id',
        ];
    }

    public function getTable(): string
    {
        return 'compass_dependabot_ymls';
    }

    /**
     * @return BelongsTo<Repository, $this>
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'package_ecosystems' => AsCollection::class,
            'repository_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): DependabotYmlFactory
    {
        return DependabotYmlFactory::new();
    }
}
