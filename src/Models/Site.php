<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Forge\DeploymentStatus;
use Maarheeze\Compass\Collector\Forge\SiteStatus;
use Maarheeze\Compass\Database\Factories\SiteFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;

use function sprintf;

/**
 * @property CarbonImmutable $collected_at
 * @property CollectionRunId $collection_run_id
 * @property CarbonImmutable $created_at
 * @property ?string $deployed_commit_hash
 * @property ?string $deployed_commit_message
 * @property ?CarbonImmutable $deployment_ended_at
 * @property ?CarbonImmutable $deployment_started_at
 * @property ?DeploymentStatus $deployment_status
 * @property string $forge_id
 * @property int $id
 * @property ?CarbonImmutable $missing_at
 * @property ?CarbonImmutable $missing_ignored_at
 * @property string $name
 * @property ?string $php_version
 * @property ?string $repository_branch
 * @property ?string $repository_url
 * @property ?string $server_ip_address
 * @property string $server_name
 * @property string $server_slug
 * @property SiteStatus $status
 * @property CarbonImmutable $updated_at
 * @property string $url
 *
 * @property-read ?Repository $repository
 */
class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    protected static string $collectionClass = SiteCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'collected_at',
            'collection_run_id',
            'deployed_commit_hash',
            'deployed_commit_message',
            'deployment_ended_at',
            'deployment_started_at',
            'deployment_status',
            'forge_id',
            'missing_at',
            'missing_ignored_at',
            'name',
            'php_version',
            'repository_branch',
            'repository_url',
            'server_ip_address',
            'server_name',
            'server_slug',
            'status',
            'url',
        ];
    }

    public function getTable(): string
    {
        return 'compass_sites';
    }

    /**
     * @return BelongsTo<Repository, $this>
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'repository_url', 'url');
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
            'deployment_ended_at' => 'immutable_datetime',
            'deployment_started_at' => 'immutable_datetime',
            'deployment_status' => DeploymentStatus::class,
            'missing_at' => 'immutable_datetime',
            'missing_ignored_at' => 'immutable_datetime',
            'status' => SiteStatus::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }
}
