<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;

class SyncCommand extends Command
{
    public const string SIGNATURE = 'compass:sync';

    public function handle(): void
    {
        $this->call(SyncGitHubCommand::SIGNATURE);
        $this->call(SyncForgeCommand::SIGNATURE);
        $this->call(CheckSiteEndpointsCommand::SIGNATURE);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Start the GitHub sync, the Forge sync and the endpoint check');
    }
}
