<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\QueuePriority;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Uuid\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

class CollectCommand extends Command
{
    public const string SIGNATURE = 'compass:collect';

    public function handle(RunRecorder $runRecorder): int
    {
        $collectorType = CollectorType::tryFrom(Assert::string($this->argument('collector')));

        if ($collectorType === null) {
            $this->error('The collector does not exist.');

            return self::FAILURE;
        }

        $queuePriority = $this->queuePriority($collectorType);

        if ($queuePriority === null) {
            $this->error('The priority does not exist.');

            return self::FAILURE;
        }

        $runId = Uuid::generate();
        $source = Assert::string($this->argument('source'));
        $job = $collectorType->job($runId, $source);

        $runRecorder->startCollector($runId, $source, $collectorType, $job, $queuePriority);

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Start a run of one collector of one source')
            ->addArgument('source', InputArgument::REQUIRED, 'The name of the source in the config')
            ->addArgument('collector', InputArgument::REQUIRED, 'The collector to run, such as repositories')
            ->addOption(
                'priority',
                null,
                InputOption::VALUE_REQUIRED,
                'The queue priority of the run: high, medium or low; without it, the scheduled priority of the collector',
            );
    }

    private function queuePriority(CollectorType $collectorType): ?QueuePriority
    {
        $priority = $this->option('priority');

        if ($priority === null) {
            return $collectorType->scheduledPriority();
        }

        return QueuePriority::tryFrom(Assert::string($priority));
    }
}
