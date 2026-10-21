<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Database\Factories\ComposerJsonPackageFactory;

/**
 * @property int $composer_json_id
 * @property string $constraint
 * @property Carbon $created_at
 * @property int $id
 * @property string $name
 * @property PackageSection $section
 * @property Carbon $updated_at
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
            'section' => PackageSection::class,
        ];
    }

    protected static function newFactory(): ComposerJsonPackageFactory
    {
        return ComposerJsonPackageFactory::new();
    }
}
