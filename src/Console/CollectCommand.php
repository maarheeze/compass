<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\CollectionStarter;
use Maarheeze\Compass\Collector\CollectionType;
use Maarheeze\Compass\Collector\SourceManager;
use Symfony\Component\Console\Input\InputArgument;
use Webmozart\Assert\Assert;

class CollectCommand extends Command
{
    public const string SIGNATURE = 'compass:collect';

    public function handle(CollectionStarter $collectionStarter, SourceManager $sourceManager): void
    {
        $collectionStarter->start(
            $sourceManager->source(Assert::string($this->argument('source'))),
            CollectionType::from(Assert::string($this->argument('collection'))),
        );
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Start a run of one collection of one source')
            ->addArgument('source', InputArgument::REQUIRED, 'The name of the source in the config')
            ->addArgument('collection', InputArgument::REQUIRED, 'The collection to run, such as repositories');
    }
}
