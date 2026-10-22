<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Maarheeze\Compass\Collections\PullRequestAssigneeCollection;
use Maarheeze\Compass\Database\Factories\PullRequestAssigneeFactory;

/**
 * @property Carbon $created_at
 * @property int $id
 * @property string $login
 * @property int $pull_request_id
 * @property Carbon $updated_at
 *
 * @property-read PullRequest $pullRequest
 */
class PullRequestAssignee extends Model
{
    /** @use HasFactory<PullRequestAssigneeFactory> */
    use HasFactory;

    protected static string $collectionClass = PullRequestAssigneeCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'login',
            'pull_request_id',
        ];
    }

    public function getTable(): string
    {
        return 'compass_pull_request_assignees';
    }

    /**
     * @return BelongsTo<PullRequest, $this>
     */
    public function pullRequest(): BelongsTo
    {
        return $this->belongsTo(PullRequest::class);
    }

    protected static function newFactory(): PullRequestAssigneeFactory
    {
        return PullRequestAssigneeFactory::new();
    }
}
