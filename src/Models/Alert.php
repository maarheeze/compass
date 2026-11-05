<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\AlertHistoryEntryCollection;
use Maarheeze\Compass\Database\Factories\AlertFactory;
use Maarheeze\Compass\Models\Builders\AlertBuilder;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property ?Uuid $assignee_id
 * @property ConditionState $condition
 * @property CarbonImmutable $created_at
 * @property ?CarbonImmutable $done_at
 * @property Uuid $id
 * @property bool $is_muted
 * @property ?CarbonImmutable $resolved_at
 * @property AlertStatus $status
 * @property Uuid $subject_id
 * @property string $subject_type
 * @property AlertType $type
 * @property CarbonImmutable $updated_at
 *
 * @property-read ?User $assignee
 * @property-read AlertHistoryEntryCollection $historyEntries
 * @property-read AlertSubject $subject
 */
class Alert extends Model
{
    /** @use HasBuilder<AlertBuilder> */
    use HasBuilder;
    /** @use HasCollection<AlertCollection> */
    use HasCollection;
    /** @use HasFactory<AlertFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $builder = AlertBuilder::class;
    protected static string $collectionClass = AlertCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'assignee_id',
            'condition',
            'done_at',
            'is_muted',
            'resolved_at',
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
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<AlertHistoryEntry, $this>
     */
    public function historyEntries(): HasMany
    {
        return $this->hasMany(AlertHistoryEntry::class);
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
            'assignee_id' => UuidCast::class,
            'condition' => ConditionState::class,
            'created_at' => 'immutable_datetime',
            'done_at' => 'immutable_datetime',
            'is_muted' => 'boolean',
            'resolved_at' => 'immutable_datetime',
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
