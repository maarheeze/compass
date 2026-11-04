<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\CollectorRegistry;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Uuid\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Webmozart\Assert\Assert;

class CollectCommand extends Command
{
    public const string SIGNATURE = 'compass:collect';

    public function handle(CollectorRegistry $collectorRegistry, RunRecorder $runRecorder): void
    {
        $runId = Uuid::generate();
        $sourceKey = Assert::string($this->argument('source'));
        $collectorType = CollectorType::from(Assert::string($this->argument('collector')));
        $job = $collectorRegistry->job($collectorType, $runId, $sourceKey);

        $runRecorder->startCollector($runId, $sourceKey, $collectorType, $job);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Start a run of one collector of one source')
            ->addArgument('source', InputArgument::REQUIRED, 'The name of the source in the config')
            ->addArgument('collector', InputArgument::REQUIRED, 'The collector to run, such as repositories');
    }
}
