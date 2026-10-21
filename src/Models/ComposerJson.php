<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Maarheeze\Compass\Collections\ComposerJsonCollection;
use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Database\Factories\ComposerJsonFactory;

/**
 * @property Carbon $created_at
 * @property int $id
 * @property string $oid
 * @property ?string $php_constraint
 * @property int $repository_id
 * @property ?string $type
 * @property Carbon $updated_at
 *
 * @property-read ComposerJsonPackageCollection $packages
 * @property-read Repository $repository
 */
class ComposerJson extends Model
{
    /** @use HasFactory<ComposerJsonFactory> */
    use HasFactory;

    protected static string $collectionClass = ComposerJsonCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'oid',
            'php_constraint',
            'repository_id',
            'type',
        ];
    }

    public function getTable(): string
    {
        return 'compass_composer_jsons';
    }

    /**
     * @return HasMany<ComposerJsonPackage, $this>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(ComposerJsonPackage::class);
    }

    /**
     * @return BelongsTo<Repository, $this>
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    protected static function newFactory(): ComposerJsonFactory
    {
        return ComposerJsonFactory::new();
    }
}
