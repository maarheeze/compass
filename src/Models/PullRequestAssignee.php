<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Collections\PullRequestAssigneeCollection;
use Maarheeze\Compass\Database\Factories\PullRequestAssigneeFactory;
use Maarheeze\Compass\Models\Builders\PullRequestAssigneeBuilder;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property string $login
 * @property Uuid $pull_request_id
 * @property CarbonImmutable $updated_at
 */
class PullRequestAssignee extends Model
{
    /** @use HasBuilder<PullRequestAssigneeBuilder> */
    use HasBuilder;
    /** @use HasCollection<PullRequestAssigneeCollection> */
    use HasCollection;
    /** @use HasFactory<PullRequestAssigneeFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $builder = PullRequestAssigneeBuilder::class;
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'pull_request_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): PullRequestAssigneeFactory
    {
        return PullRequestAssigneeFactory::new();
    }
}
