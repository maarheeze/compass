<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Maarheeze\Compass\Collections\ComposerLockCollection;
use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Database\Factories\ComposerLockFactory;

/**
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property string $oid
 * @property ?string $php_constraint
 * @property int $repository_id
 * @property CarbonImmutable $updated_at
 *
 * @property-read ComposerLockPackageCollection $packages
 * @property-read Repository $repository
 */
class ComposerLock extends Model
{
    /** @use HasFactory<ComposerLockFactory> */
    use HasFactory;

    protected static string $collectionClass = ComposerLockCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'oid',
            'php_constraint',
            'repository_id',
        ];
    }

    public function getTable(): string
    {
        return 'compass_composer_locks';
    }

    /**
     * @return HasMany<ComposerLockPackage, $this>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(ComposerLockPackage::class);
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
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): ComposerLockFactory
    {
        return ComposerLockFactory::new();
    }
}
