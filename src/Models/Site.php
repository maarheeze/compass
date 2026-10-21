<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Forge\DeploymentStatus;
use Maarheeze\Compass\Collector\Forge\SiteStatus;
use Maarheeze\Compass\Database\Factories\SiteFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;

use function sprintf;

/**
 * @property Carbon $collected_at
 * @property CollectionRunId $collection_run_id
 * @property Carbon $created_at
 * @property ?string $deployed_commit_hash
 * @property ?string $deployed_commit_message
 * @property ?Carbon $deployment_ended_at
 * @property ?Carbon $deployment_started_at
 * @property ?DeploymentStatus $deployment_status
 * @property string $forge_id
 * @property int $id
 * @property bool $is_active
 * @property string $name
 * @property ?string $php_version
 * @property ?string $repository_branch
 * @property ?string $repository_url
 * @property ?string $server_ip_address
 * @property string $server_name
 * @property string $server_slug
 * @property SiteStatus $status
 * @property Carbon $updated_at
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
            'is_active',
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
            'collected_at' => 'datetime',
            'collection_run_id' => sprintf('%s:%s', UuidCast::class, CollectionRunId::class),
            'deployment_ended_at' => 'datetime',
            'deployment_started_at' => 'datetime',
            'deployment_status' => DeploymentStatus::class,
            'is_active' => 'boolean',
            'status' => SiteStatus::class,
        ];
    }

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }
}
