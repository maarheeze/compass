@switch ($deploymentState)
    @case (\Maarheeze\Compass\Analyzer\Forge\DeploymentState::BEHIND)
        <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20">Behind</span>
        @break
    @case (\Maarheeze\Compass\Analyzer\Forge\DeploymentState::UP_TO_DATE)
        <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20">Up to date</span>
        @break
@endswitch
