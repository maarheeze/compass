<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\Database\Factories\AlertCheckFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property string $alert_type_identifier
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
class AlertCheck extends Model
{
    /** @use HasCollection<AlertCheckCollection> */
    use HasCollection;
    /** @use HasFactory<AlertCheckFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = AlertCheckCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'alert_type_identifier',
            'condition',
            'observed_at',
            'subject_id',
            'subject_type',
        ];
    }

    public function getTable(): string
    {
        return 'compass_alert_checks';
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
            'observed_at' => 'immutable_datetime',
            'subject_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): AlertCheckFactory
    {
        return AlertCheckFactory::new();
    }
}
