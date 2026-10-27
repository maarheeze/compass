<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Collections\RunCollection;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Database\Factories\RunFactory;
use Maarheeze\Uuid\Laravel\Model\Casts\UuidCast;

use function sprintf;

/**
 * @property string $batch_id
 * @property CarbonImmutable $created_at
 * @property ?int $failed_job_count
 * @property ?CarbonImmutable $finished_at
 * @property RunId $id
 * @property RunType $type
 * @property CarbonImmutable $updated_at
 */
class Run extends Model
{
    /** @use HasFactory<RunFactory> */
    use HasFactory;

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
            'type',
        ];
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getTable(): string
    {
        return 'compass_runs';
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
            'id' => sprintf('%s:%s', UuidCast::class, RunId::class),
            'type' => RunType::class,
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): RunFactory
    {
        return RunFactory::new();
    }
}
