<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Models\Repository;

class CollectChangedComposerJsons implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly RunId $runId,
    ) {
    }

    public function handle(Dispatcher $dispatcher): void
    {
        $repositoriesWithChangedComposerJson = Repository::query()
            ->where('run_id', $this->runId)
            ->whereNotNull('composer_json_oid')
            ->whereDoesntHave('composerJson', static function (Builder $composerJsonQuery): void {
                $composerJsonQuery->whereColumn('oid', 'compass_repositories.composer_json_oid');
            })
            ->get();

        $repositoriesWithRemovedComposerJson = Repository::query()
            ->where('run_id', $this->runId)
            ->whereNull('composer_json_oid')
            ->has('composerJson')
            ->get();

        foreach ($repositoriesWithChangedComposerJson->merge($repositoriesWithRemovedComposerJson) as $repository) {
            $dispatcher->dispatch(new CollectComposerJson($repository));
        }
    }
}
