<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Database\Factories\ComposerLockPackageFactory;

/**
 * @property int $composer_lock_id
 * @property Carbon $created_at
 * @property int $id
 * @property string $name
 * @property PackageSection $section
 * @property Carbon $updated_at
 * @property string $version
 *
 * @property-read ComposerLock $composerLock
 */
class ComposerLockPackage extends Model
{
    /** @use HasFactory<ComposerLockPackageFactory> */
    use HasFactory;

    protected static string $collectionClass = ComposerLockPackageCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'composer_lock_id',
            'name',
            'section',
            'version',
        ];
    }

    public function getTable(): string
    {
        return 'compass_composer_lock_packages';
    }

    /**
     * @return BelongsTo<ComposerLock, $this>
     */
    public function composerLock(): BelongsTo
    {
        return $this->belongsTo(ComposerLock::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'section' => PackageSection::class,
        ];
    }

    protected static function newFactory(): ComposerLockPackageFactory
    {
        return ComposerLockPackageFactory::new();
    }
}
