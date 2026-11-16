<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Listeners;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\Events\JobFailed;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Models\Run;
use Throwable;

use function array_key_exists;
use function array_values;
use function in_array;
use function is_array;
use function is_string;

readonly class RecordRunFailure
{
    public function __construct(
        private Context $context,
    ) {
    }

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

        $run->failures()->create([
            'context' => array_values($this->context->except([RunRecorder::RUN_ID_CONTEXT_KEY])),
            'messages' => $this->messages($jobFailed->exception),
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

    /**
     * @return list<string>
     */
    private function messages(Throwable $exception): array
    {
        $messages = [];
        $chainedException = $exception;

        while ($chainedException instanceof Throwable) {
            $message = $chainedException->getMessage();

            if ($message !== '' && !in_array($message, $messages, true)) {
                $messages[] = $message;
            }

            $chainedException = $chainedException->getPrevious();
        }

        return $messages;
    }
}
