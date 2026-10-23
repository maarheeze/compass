<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Maarheeze\Compass\Collections\PullRequestAssigneeCollection;
use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Database\Factories\PullRequestFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;

use function sprintf;

/**
 * @property ?string $author_login
 * @property CarbonImmutable $collected_at
 * @property CollectionRunId $collection_run_id
 * @property CarbonImmutable $created_at
 * @property string $github_id
 * @property int $id
 * @property bool $is_draft
 * @property int $number
 * @property CarbonImmutable $opened_at
 * @property int $repository_id
 * @property string $title
 * @property CarbonImmutable $updated_at
 * @property string $url
 *
 * @property-read PullRequestAssigneeCollection $assignees
 * @property-read Repository $repository
 */
class PullRequest extends Model
{
    /** @use HasFactory<PullRequestFactory> */
    use HasFactory;

    protected static string $collectionClass = PullRequestCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'author_login',
            'collected_at',
            'collection_run_id',
            'github_id',
            'is_draft',
            'number',
            'opened_at',
            'repository_id',
            'title',
            'url',
        ];
    }

    public function getTable(): string
    {
        return 'compass_pull_requests';
    }

    /**
     * @return HasMany<PullRequestAssignee, $this>
     */
    public function assignees(): HasMany
    {
        return $this->hasMany(PullRequestAssignee::class);
    }

    /**
     * @return BelongsTo<Repository, $this>
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    /**
     * @param Builder<self> $pullRequestQuery
     */
    public function scopeAssignedTo(Builder $pullRequestQuery, User $user): void
    {
        $pullRequestQuery->whereHas('assignees', static function (Builder $assigneeQuery) use ($user): void {
            $assigneeQuery->where('login', $user->nickname);
        });
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
            'is_draft' => 'boolean',
            'number' => 'integer',
            'opened_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): PullRequestFactory
    {
        return PullRequestFactory::new();
    }
}
