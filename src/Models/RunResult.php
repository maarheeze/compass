<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collections\RunResultCollection;
use Maarheeze\Compass\Collector\RunResultStatus;
use Maarheeze\Compass\Database\Factories\RunResultFactory;
use Maarheeze\Compass\Models\Concerns\HasSubject;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property CarbonImmutable $created_at
 * @property Uuid $id
 * @property list<string> $messages
 * @property Uuid $run_id
 * @property RunResultStatus $status
 * @property Uuid $subject_id
 * @property string $subject_type
 * @property CarbonImmutable $updated_at
 *
 * @property-read AlertSubject $subject
 */
class RunResult extends Model
{
    /** @use HasCollection<RunResultCollection> */
    use HasCollection;
    /** @use HasFactory<RunResultFactory> */
    use HasFactory;
    use HasSubject;
    use HasUuidAsId;

    protected static string $collectionClass = RunResultCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'messages',
            'run_id',
            'status',
        ];
    }

    public function getTable(): string
    {
        return 'compass_run_results';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'messages' => 'array',
            'run_id' => UuidCast::class,
            'status' => RunResultStatus::class,
            'subject_id' => UuidCast::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): RunResultFactory
    {
        return RunResultFactory::new();
    }
}
