<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Maarheeze\Compass\Collections\ProjectCollection;
use Maarheeze\Compass\Database\Factories\ProjectFactory;

/**
 * @property Carbon $collected_at
 * @property string $commit_sha
 * @property Carbon $committed_at
 * @property Carbon $created_at
 * @property string $default_branch
 * @property int $id
 * @property string $repository
 * @property Carbon $updated_at
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected static string $collectionClass = ProjectCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'commit_sha',
            'committed_at',
            'collected_at',
            'default_branch',
            'repository',
        ];
    }

    public function getTable(): string
    {
        return 'compass_projects';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collected_at' => 'datetime',
            'committed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }
}
