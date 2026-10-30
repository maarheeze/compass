<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Collections\ObservationCollection;
use Maarheeze\Compass\Database\Factories\ObservationFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property ?Uuid $alert_id
 * @property AlertType $alert_type
 * @property ConditionState $condition
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property CarbonImmutable $observed_at
 * @property Uuid $subject_id
 * @property string $subject_type
 * @property CarbonImmutable $updated_at
 *
 * @property-read AlertSubject $subject
 */
class Observation extends Model
{
    /** @use HasCollection<ObservationCollection> */
    use HasCollection;
    /** @use HasFactory<ObservationFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = ObservationCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'alert_id',
            'alert_type',
            'condition',
            'observed_at',
            'subject_id',
            'subject_type',
        ];
    }

    public function getTable(): string
    {
        return 'compass_observations';
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
            'alert_id' => UuidCast::class,
            'alert_type' => AlertType::class,
            'condition' => ConditionState::class,
            'created_at' => 'immutable_datetime',
            'observed_at' => 'immutable_datetime',
            'subject_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): ObservationFactory
    {
        return ObservationFactory::new();
    }
}
