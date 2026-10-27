@switch ($deploymentState)
    @case (\Maarheeze\Compass\Analyzer\Forge\DeploymentState::BEHIND)
        <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20">{{ __('compass::deployment-state.BEHIND') }}</span>
        @break
    @case (\Maarheeze\Compass\Analyzer\Forge\DeploymentState::DEPLOYING)
        <span class="rounded-md bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700 ring-1 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-400 dark:ring-sky-400/20">{{ __('compass::deployment-state.DEPLOYING') }}</span>
        @break
    @case (\Maarheeze\Compass\Analyzer\Forge\DeploymentState::DEPLOYMENT_OVERDUE)
        <span class="rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20">{{ __('compass::deployment-state.DEPLOYMENT_OVERDUE') }}</span>
        @break
    @case (\Maarheeze\Compass\Analyzer\Forge\DeploymentState::UP_TO_DATE)
        <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20">{{ __('compass::deployment-state.UP_TO_DATE') }}</span>
        @break
@endswitch
