<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Collections\IssueAssigneeCollection;
use Maarheeze\Compass\Database\Factories\IssueAssigneeFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property Uuid $issue_id
 * @property string $login
 * @property CarbonImmutable $updated_at
 */
class IssueAssignee extends Model
{
    /** @use HasFactory<IssueAssigneeFactory> */
    use HasFactory;
    use HasUuidAsId;

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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'issue_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): IssueAssigneeFactory
    {
        return IssueAssigneeFactory::new();
    }
}
