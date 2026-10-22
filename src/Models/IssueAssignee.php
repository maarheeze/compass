<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Maarheeze\Compass\Collections\IssueAssigneeCollection;
use Maarheeze\Compass\Database\Factories\IssueAssigneeFactory;

/**
 * @property Carbon $created_at
 * @property int $id
 * @property int $issue_id
 * @property string $login
 * @property Carbon $updated_at
 *
 * @property-read Issue $issue
 */
class IssueAssignee extends Model
{
    /** @use HasFactory<IssueAssigneeFactory> */
    use HasFactory;

    protected static string $collectionClass = IssueAssigneeCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'issue_id',
            'login',
        ];
    }

    public function getTable(): string
    {
        return 'compass_issue_assignees';
    }

    /**
     * @return BelongsTo<Issue, $this>
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    protected static function newFactory(): IssueAssigneeFactory
    {
        return IssueAssigneeFactory::new();
    }
}
