<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collections\IssueAssigneeCollection;
use Maarheeze\Compass\Database\Factories\IssueAssigneeFactory;

/**
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property int $issue_id
 * @property string $login
 * @property CarbonImmutable $updated_at
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): IssueAssigneeFactory
    {
        return IssueAssigneeFactory::new();
    }
}
