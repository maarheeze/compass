<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\HasCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Maarheeze\Compass\Collections\RunCollection;
use Maarheeze\Compass\Collections\RunFailureCollection;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Database\Factories\RunFactory;
use Maarheeze\Compass\Models\Builders\RunBuilder;
use Maarheeze\Uuid\Laravel\Model\Concerns\HasUuidAsId;
use Maarheeze\Uuid\Uuid;

/**
 * @property string $batch_id
 * @property CarbonImmutable $created_at
 * @property ?int $failed_job_count
 * @property ?CarbonImmutable $finished_at
 * @property Uuid $id
 * @property RunStatus $status
 * @property RunType $type
 * @property CarbonImmutable $updated_at
 *
 * @property-read RunFailureCollection $failures
 */
class Run extends Model
{
    /** @use HasBuilder<RunBuilder> */
    use HasBuilder;
    /** @use HasCollection<RunCollection> */
    use HasCollection;
    /** @use HasFactory<RunFactory> */
    use HasFactory;
    use HasUuidAsId;

    protected static string $builder = RunBuilder::class;
    protected static string $collectionClass = RunCollection::class;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'batch_id',
            'failed_job_count',
            'finished_at',
            'id',
            'status',
            'type',
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
            'created_at' => 'immutable_datetime',
            'failed_job_count' => 'integer',
            'finished_at' => 'immutable_datetime',
            'status' => RunStatus::class,
            'type' => RunType::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): RunFactory
    {
        return RunFactory::new();
    }
}
