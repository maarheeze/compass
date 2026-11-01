<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Collections\RunFailureCollection;
use Maarheeze\Compass\Database\Factories\RunFailureFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property string $message
 * @property Uuid $run_id
 * @property CarbonImmutable $updated_at
 */
class RunFailure extends Model
{
    /** @use HasCollection<RunFailureCollection> */
    use HasCollection;
    /** @use HasFactory<RunFailureFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = RunFailureCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'message',
            'run_id',
        ];
    }

    public function getTable(): string
    {
        return 'compass_run_failures';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'run_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): RunFailureFactory
    {
        return RunFailureFactory::new();
    }
}
