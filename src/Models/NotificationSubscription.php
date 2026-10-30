<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Database\Factories\NotificationSubscriptionFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property AlertType $alert_type
 * @property NotificationChannel $channel
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property CarbonImmutable $updated_at
 * @property Uuid $user_id
 *
 * @property-read User $user
 */
class NotificationSubscription extends Model
{
    /** @use HasCollection<NotificationSubscriptionCollection> */
    use HasCollection;
    /** @use HasFactory<NotificationSubscriptionFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = NotificationSubscriptionCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'alert_type',
            'channel',
            'user_id',
        ];
    }

    public function getTable(): string
    {
        return 'compass_notification_subscriptions';
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
            'alert_type' => AlertType::class,
            'channel' => NotificationChannel::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'user_id' => UuidCast::class,
        ];
    }

    protected static function newFactory(): NotificationSubscriptionFactory
    {
        return NotificationSubscriptionFactory::new();
    }
}
