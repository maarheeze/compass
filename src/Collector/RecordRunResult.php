<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Closure;
use Illuminate\Log\Context\Repository as Context;
use Maarheeze\Compass\Models\RunResult;
use Maarheeze\Uuid\Uuid;
use Throwable;
use Webmozart\Assert\Assert;

readonly class RecordRunResult
{
    public function __construct(
        private Context $context,
        private ExceptionMessages $exceptionMessages,
    ) {
    }

    /**
     * @param Closure(LockedJob): mixed $next
     *
     * @throws Throwable
     */
    public function handle(LockedJob $lockedJob, Closure $next): void
    {
        if (!$lockedJob instanceof SubjectJob) {
            $next($lockedJob);

            return;
        }

        try {
            $next($lockedJob);
        } catch (RequestBudgetExhausted $requestBudgetExhausted) {
            throw $requestBudgetExhausted;
        } catch (Throwable $exception) {
            $this->storeRunResult($lockedJob, RunResultStatus::FAILED, $this->exceptionMessages->messages($exception));

            throw $exception;
        }

        if ($lockedJob->skipsSubject()) {
            return;
        }

        $this->storeRunResult($lockedJob, RunResultStatus::SUCCEEDED, []);
    }

    /**
     * @param list<string> $messages
     */
    private function storeRunResult(SubjectJob $subjectJob, RunResultStatus $runResultStatus, array $messages): void
    {
        $runResult = new RunResult([
            'run_id' => Uuid::fromString(Assert::string($this->context->get(RunRecorder::RUN_ID_CONTEXT_KEY))),
            'status' => $runResultStatus,
            'messages' => $messages,
        ]);
        $runResult->subject()->associate($subjectJob->subject());
        $runResult->save();
    }
}
