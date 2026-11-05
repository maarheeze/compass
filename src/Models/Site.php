<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collections\SiteEndpointCheckCollection;
use Maarheeze\Compass\Collections\SiteReportCollection;
use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use Maarheeze\Compass\Database\Factories\SiteFactory;
use Maarheeze\Compass\Models\Builders\AlertBuilder;
use Maarheeze\Compass\Models\Builders\SiteBuilder;
use Maarheeze\Compass\Models\Concerns\HasAlerts;
use Maarheeze\Compass\Models\Concerns\HasMissingState;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property ?bool $auto_deploy
 * @property int $auto_deploy_grace_minutes
 * @property CarbonImmutable $collected_at
 * @property CarbonImmutable $created_at
 * @property ?string $deployed_commit_hash
 * @property ?CarbonImmutable $deployment_ended_at
 * @property ?CarbonImmutable $deployment_started_at
 * @property ?DeploymentStatus $deployment_status
 * @property string $forge_id
 * @property Uuid $id
 * @property ?CarbonImmutable $missing_at
 * @property ?CarbonImmutable $missing_ignored_at
 * @property string $name
 * @property ?string $php_version
 * @property ?string $repository_branch
 * @property ?string $repository_url
 * @property Uuid $run_id
 * @property ?string $server_ip_address
 * @property string $server_name
 * @property string $source
 * @property CarbonImmutable $updated_at
 * @property string $url
 *
 * @property-read AlertCollection $alerts
 * @property-read SiteEndpointCheckCollection $endpointChecks
 * @property-read SiteReportCollection $reports
 * @property-read ?Repository $repository
 */
class Site extends Model implements AlertSubject
{
    use HasAlerts;
    /** @use HasBuilder<SiteBuilder> */
    use HasBuilder;
    /** @use HasCollection<SiteCollection> */
    use HasCollection;
    /** @use HasFactory<SiteFactory> */
    use HasFactory;
    use HasMissingState;
    use HasUuidAsId;

    protected static string $builder = SiteBuilder::class;
    protected static string $collectionClass = SiteCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'auto_deploy',
            'auto_deploy_grace_minutes',
            'collected_at',
            'deployed_commit_hash',
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
            'run_id',
            'server_ip_address',
            'server_name',
            'source',
            'url',
        ];
    }

    public function getTable(): string
    {
        return 'compass_sites';
    }

    /**
     * @return HasMany<SiteEndpointCheck, $this>
     */
    public function endpointChecks(): HasMany
    {
        return $this->hasMany(SiteEndpointCheck::class);
    }

    /**
     * @return HasMany<SiteReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(SiteReport::class);
    }

    /**
     * @return BelongsTo<Repository, $this>
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'repository_url', 'url');
    }

    public function alertsIncludingChildren(): AlertBuilder
    {
        return Alert::query()->ofSite($this);
    }

    public function displayName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auto_deploy' => 'boolean',
            'auto_deploy_grace_minutes' => 'integer',
            'collected_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'deployment_ended_at' => 'immutable_datetime',
            'deployment_started_at' => 'immutable_datetime',
            'deployment_status' => DeploymentStatus::class,
            'missing_at' => 'immutable_datetime',
            'missing_ignored_at' => 'immutable_datetime',
            'run_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }
}
