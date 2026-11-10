<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Maarheeze\Compass\Collections\RunCollection;
use Maarheeze\Compass\Collections\RunFailureCollection;
use Maarheeze\Compass\Collector\Events\RunSaved;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Database\Factories\RunFactory;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property ?string $batch_id
 * @property ?CollectorType $collector
 * @property CarbonImmutable $created_at
 * @property ?int $failed_job_count
 * @property ?CarbonImmutable $finished_at
 * @property Uuid $id
 * @property ?string $source
 * @property RunStatus $status
 * @property ?TaskType $task
 * @property CarbonImmutable $updated_at
 *
 * @property-read RunFailureCollection $failures
 */
class Run extends Model
{
    /** @use HasCollection<RunCollection> */
    use HasCollection;
    /** @use HasFactory<RunFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $collectionClass = RunCollection::class;

    /** @var array<string, class-string> */
    protected $dispatchesEvents = ['saved' => RunSaved::class];

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'batch_id',
            'collector',
            'failed_job_count',
            'finished_at',
            'id',
            'source',
            'status',
            'task',
        ];
    }

    public function getTable(): string
    {
        return 'compass_runs';
    }

    /**
     * @return HasMany<RunFailure, $this>
     */
    public function failures(): HasMany
    {
        return $this->hasMany(RunFailure::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collector' => CollectorType::class,
            'created_at' => 'immutable_datetime',
            'failed_job_count' => 'integer',
            'finished_at' => 'immutable_datetime',
            'status' => RunStatus::class,
            'task' => TaskType::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): RunFactory
    {
        return RunFactory::new();
    }
}
