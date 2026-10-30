<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Collections\AlertHistoryEntryCollection;
use Maarheeze\Compass\Database\Factories\AlertHistoryEntryFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property Uuid $alert_id
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property CarbonImmutable $occurred_at
 * @property AlertHistoryEntryType $type
 * @property CarbonImmutable $updated_at
 * @property ?Uuid $user_id
 *
 * @property-read ?User $user
 */
class AlertHistoryEntry extends Model
{
    /** @use HasCollection<AlertHistoryEntryCollection> */
    use HasCollection;
    /** @use HasFactory<AlertHistoryEntryFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = AlertHistoryEntryCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'alert_id',
            'occurred_at',
            'type',
            'user_id',
        ];
    }

    public function getTable(): string
    {
        return 'compass_alert_history_entries';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alert_id' => UuidCast::class,
            'created_at' => 'immutable_datetime',
            'occurred_at' => 'immutable_datetime',
            'type' => AlertHistoryEntryType::class,
            'updated_at' => 'immutable_datetime',
            'user_id' => UuidCast::class,
        ];
    }

    protected static function newFactory(): AlertHistoryEntryFactory
    {
        return AlertHistoryEntryFactory::new();
    }
}
