<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Database\Factories\AlertFactory;
use Maarheeze\Compass\Models\Builders\AlertBuilder;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property ConditionState $condition
 * @property CarbonImmutable $created_at
 * @property ?CarbonImmutable $done_at
 * @property ?CarbonImmutable $gone_at
 * @property Uuid $id
 * @property AlertStatus $status
 * @property Uuid $subject_id
 * @property string $subject_type
 * @property AlertType $type
 * @property CarbonImmutable $updated_at
 *
 * @property-read AlertSubject $subject
 */
class Alert extends Model
{
    /** @use HasFactory<AlertFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = AlertCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'condition',
            'done_at',
            'gone_at',
            'status',
            'subject_id',
            'subject_type',
            'type',
        ];
    }

    public function getTable(): string
    {
        return 'compass_alerts';
    }

    /**
     * @param QueryBuilder $query
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function newEloquentBuilder($query): AlertBuilder
    {
        return new AlertBuilder($query);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'condition' => ConditionState::class,
            'created_at' => 'immutable_datetime',
            'done_at' => 'immutable_datetime',
            'gone_at' => 'immutable_datetime',
            'status' => AlertStatus::class,
            'subject_id' => UuidCast::class,
            'type' => AlertType::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): AlertFactory
    {
        return AlertFactory::new();
    }
}
