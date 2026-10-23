<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Database\Factories\ComposerJsonPackageFactory;

/**
 * @property int $composer_json_id
 * @property string $constraint
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property string $name
 * @property PackageSection $section
 * @property CarbonImmutable $updated_at
 *
 * @property-read ComposerJson $composerJson
 */
class ComposerJsonPackage extends Model
{
    /** @use HasFactory<ComposerJsonPackageFactory> */
    use HasFactory;

    protected static string $collectionClass = ComposerJsonPackageCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'composer_json_id',
            'constraint',
            'name',
            'section',
        ];
    }

    public function getTable(): string
    {
        return 'compass_composer_json_packages';
    }

    /**
     * @return BelongsTo<ComposerJson, $this>
     */
    public function composerJson(): BelongsTo
    {
        return $this->belongsTo(ComposerJson::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'section' => PackageSection::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): ComposerJsonPackageFactory
    {
        return ComposerJsonPackageFactory::new();
    }
}
