<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Database\Factories\RepositoryFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;

use function sprintf;

/**
 * @property CarbonImmutable $collected_at
 * @property CollectionRunId $collection_run_id
 * @property ?string $composer_json_oid
 * @property ?string $composer_lock_oid
 * @property CarbonImmutable $created_at
 * @property string $default_branch
 * @property string $github_id
 * @property int $id
 * @property string $last_commit_sha
 * @property ?CarbonImmutable $missing_at
 * @property ?CarbonImmutable $missing_ignored_at
 * @property string $name
 * @property CarbonImmutable $updated_at
 * @property string $url
 * @property RepositoryVisibility $visibility
 *
 * @property-read ?ComposerJson $composerJson
 * @property-read ?ComposerLock $composerLock
 * @property-read IssueCollection $issues
 * @property-read PullRequestCollection $pullRequests
 * @property-read SiteCollection $sites
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
            'composer_json_oid',
            'composer_lock_oid',
            'default_branch',
            'github_id',
            'last_commit_sha',
            'missing_at',
            'missing_ignored_at',
            'name',
            'url',
            'visibility',
        ];
    }

    /**
     * @return HasOne<ComposerJson, $this>
     */
    public function composerJson(): HasOne
    {
        return $this->hasOne(ComposerJson::class);
    }

    /**
     * @return HasOne<ComposerLock, $this>
     */
    public function composerLock(): HasOne
    {
        return $this->hasOne(ComposerLock::class);
    }

    /**
     * @return HasMany<Issue, $this>
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /**
     * @return HasMany<PullRequest, $this>
     */
    public function pullRequests(): HasMany
    {
        return $this->hasMany(PullRequest::class);
    }

    /**
     * @return HasMany<Site, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class, 'repository_url', 'url');
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
            'collected_at' => 'immutable_datetime',
            'collection_run_id' => sprintf('%s:%s', UuidCast::class, CollectionRunId::class),
            'created_at' => 'immutable_datetime',
            'missing_at' => 'immutable_datetime',
            'missing_ignored_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'visibility' => RepositoryVisibility::class,
        ];
    }

    protected static function newFactory(): RepositoryFactory
    {
        return RepositoryFactory::new();
    }
}
