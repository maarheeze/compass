<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
    /** @use HasFactory<PullRequestAssigneeFactory> */
    use HasFactory;
    use HasUuidAsId;

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
     * @param QueryBuilder $query
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function newEloquentBuilder($query): PullRequestAssigneeBuilder
    {
        return new PullRequestAssigneeBuilder($query);
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
