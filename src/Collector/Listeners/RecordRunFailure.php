<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Listeners;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\Events\JobFailed;
use Maarheeze\Compass\Collector\ExceptionMessages;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\SubjectJob;
use Maarheeze\Compass\Models\Run;

use function array_key_exists;
use function array_values;
use function is_a;
use function is_array;
use function is_string;

readonly class RecordRunFailure
{
    public function __construct(
        private Context $context,
        private ExceptionMessages $exceptionMessages,
    ) {
    }

    public function handle(JobFailed $jobFailed): void
    {
        $batchId = $this->batchId($jobFailed->job);

        if ($batchId === null || $this->isSubjectJob($jobFailed->job)) {
            return;
        }

        $run = Run::query()
            ->where('batch_id', $batchId)
            ->first();

        if (!$run instanceof Run) {
            return;
        }

        $run->failures()->create([
            'context' => array_values($this->context->except([RunRecorder::RUN_ID_CONTEXT_KEY])),
            'messages' => $this->exceptionMessages->messages($jobFailed->exception),
        ]);
    }

    private function batchId(Job $job): ?string
    {
        $jobData = $job->payload()['data'];

        if (!is_array($jobData) || !array_key_exists('batchId', $jobData) || !is_string($jobData['batchId'])) {
            return null;
        }

        return $jobData['batchId'];
    }

    private function isSubjectJob(Job $job): bool
    {
        $jobData = $job->payload()['data'];

        return is_array($jobData)
            && array_key_exists('commandName', $jobData)
            && is_string($jobData['commandName'])
            && is_a($jobData['commandName'], SubjectJob::class, true);
    }
}
