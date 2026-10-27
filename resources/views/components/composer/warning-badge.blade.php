@if ($composerWarning)
    <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20">
        @switch ($composerWarning)
            @case (\Maarheeze\Compass\Analyzer\Composer\ComposerWarning::LIBRARY_WITH_LOCK)
                {{ __('compass::composer-warning.LIBRARY_WITH_LOCK') }}
                @break
            @case (\Maarheeze\Compass\Analyzer\Composer\ComposerWarning::MISSING_TYPE)
                {{ __('compass::composer-warning.MISSING_TYPE') }}
                @break
            @case (\Maarheeze\Compass\Analyzer\Composer\ComposerWarning::PROJECT_WITHOUT_LOCK)
                {{ __('compass::composer-warning.PROJECT_WITHOUT_LOCK') }}
                @break
            @case (\Maarheeze\Compass\Analyzer\Composer\ComposerWarning::UNKNOWN_TYPE)
                {{ __('compass::composer-warning.UNKNOWN_TYPE') }}
                @break
        @endswitch
    </span>
@endif
