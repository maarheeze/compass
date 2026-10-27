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
use Maarheeze\Compass\Database\Factories\PullRequestFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property ?string $author_login
 * @property CarbonImmutable $collected_at
 * @property CarbonImmutable $created_at
 * @property string $github_id
 * @property Uuid $id
 * @property bool $is_draft
 * @property int $number
 * @property CarbonImmutable $opened_at
 * @property Uuid $repository_id
 * @property Uuid $run_id
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
    use HasUuidAsId;

    protected static string $collectionClass = PullRequestCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'author_login',
            'collected_at',
            'github_id',
            'is_draft',
            'number',
            'opened_at',
            'repository_id',
            'run_id',
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
            'created_at' => 'immutable_datetime',
            'is_draft' => 'boolean',
            'number' => 'integer',
            'opened_at' => 'immutable_datetime',
            'repository_id' => UuidCast::class,
            'run_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): PullRequestFactory
    {
        return PullRequestFactory::new();
    }
}
