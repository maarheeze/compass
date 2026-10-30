<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Collections\ObservationCollection;
use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Database\Factories\RepositoryFactory;
use Maarheeze\Compass\Models\Concerns\HasAlerts;
use Maarheeze\Compass\Models\Concerns\HasMissingState;
use Maarheeze\Compass\Models\Concerns\HasObservations;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $collected_at
 * @property ?string $composer_json_oid
 * @property ?string $composer_lock_oid
 * @property CarbonImmutable $created_at
 * @property string $default_branch
 * @property string $github_id
 * @property Uuid $id
 * @property CarbonImmutable $last_commit_committed_at
 * @property string $last_commit_sha
 * @property ?CarbonImmutable $missing_at
 * @property ?CarbonImmutable $missing_ignored_at
 * @property string $name
 * @property Uuid $run_id
 * @property CarbonImmutable $updated_at
 * @property string $url
 * @property RepositoryVisibility $visibility
 *
 * @property-read AlertCollection $alerts
 * @property-read ?ComposerJson $composerJson
 * @property-read ?ComposerLock $composerLock
 * @property-read IssueCollection $issues
 * @property-read ObservationCollection $observations
 * @property-read PullRequestCollection $pullRequests
 * @property-read SiteCollection $sites
 */
class Repository extends Model implements AlertSubject
{
    use HasAlerts;
    /** @use HasFactory<RepositoryFactory> */
    use HasFactory;
    use HasMissingState;
    use HasObservations;
    use HasUuidAsId;

    protected static string $collectionClass = RepositoryCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'collected_at',
            'composer_json_oid',
            'composer_lock_oid',
            'default_branch',
            'github_id',
            'last_commit_committed_at',
            'last_commit_sha',
            'missing_at',
            'missing_ignored_at',
            'name',
            'run_id',
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

    public function displayName(): string
    {
        return $this->name;
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
            'created_at' => 'immutable_datetime',
            'last_commit_committed_at' => 'immutable_datetime',
            'missing_at' => 'immutable_datetime',
            'missing_ignored_at' => 'immutable_datetime',
            'run_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
            'visibility' => RepositoryVisibility::class,
        ];
    }

    protected static function newFactory(): RepositoryFactory
    {
        return RepositoryFactory::new();
    }
}
