<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Database\Factories\RepositoryFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;

use function sprintf;

/**
 * @property Carbon $collected_at
 * @property CollectionRunId $collection_run_id
 * @property Carbon $created_at
 * @property string $default_branch
 * @property int $id
 * @property bool $is_active
 * @property string $name
 * @property Carbon $updated_at
 */
class Repository extends Model
{
    /** @use HasFactory<RepositoryFactory> */
    use HasFactory;

    protected static string $collectionClass = RepositoryCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'collected_at',
            'collection_run_id',
            'default_branch',
            'is_active',
            'name',
        ];
    }

    public function getTable(): string
    {
        return 'compass_repositories';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collected_at' => 'datetime',
            'collection_run_id' => sprintf('%s:%s', UuidCast::class, CollectionRunId::class),
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): RepositoryFactory
    {
        return RepositoryFactory::new();
    }
}
