<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Listeners;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Maarheeze\Compass\Models\Run;

use function array_key_exists;
use function is_array;
use function is_string;

readonly class RecordRunFailure
{
    public function handle(JobFailed $jobFailed): void
    {
        $batchId = $this->batchId($jobFailed->job);

        if ($batchId === null) {
            return;
        }

        $run = Run::query()
            ->where('batch_id', $batchId)
            ->first();

        if (!$run instanceof Run) {
            return;
        }

        $run->failures()->create(['message' => $jobFailed->exception->getMessage()]);
    }

    private function batchId(Job $job): ?string
    {
        $jobData = $job->payload()['data'];

        if (!is_array($jobData) || !array_key_exists('batchId', $jobData) || !is_string($jobData['batchId'])) {
            return null;
        }

        return $jobData['batchId'];
    }
}
