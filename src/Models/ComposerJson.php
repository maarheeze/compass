<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Maarheeze\Compass\Collections\ComposerJsonCollection;
use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Database\Factories\ComposerJsonFactory;

/**
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property string $oid
 * @property ?string $php_constraint
 * @property int $repository_id
 * @property ?string $type
 * @property CarbonImmutable $updated_at
 *
 * @property-read ComposerJsonPackageCollection $developmentPackages
 * @property-read ComposerJsonPackageCollection $packages
 * @property-read ComposerJsonPackageCollection $productionPackages
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
    public function developmentPackages(): HasMany
    {
        return $this->hasMany(ComposerJsonPackage::class)->where('section', PackageSection::DEVELOPMENT);
    }

    /**
     * @return HasMany<ComposerJsonPackage, $this>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(ComposerJsonPackage::class);
    }

    /**
     * @return HasMany<ComposerJsonPackage, $this>
     */
    public function productionPackages(): HasMany
    {
        return $this->hasMany(ComposerJsonPackage::class)->where('section', PackageSection::PRODUCTION);
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

    protected static function newFactory(): ComposerJsonFactory
    {
        return ComposerJsonFactory::new();
    }
}
