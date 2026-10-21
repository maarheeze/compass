@if ($composerWarning)
    <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20">
        @switch ($composerWarning)
            @case (\Maarheeze\Compass\Analyzer\Composer\ComposerWarning::LIBRARY_WITH_LOCK)
                Library with a committed composer.lock
                @break
            @case (\Maarheeze\Compass\Analyzer\Composer\ComposerWarning::MISSING_TYPE)
                composer.json has no type
                @break
            @case (\Maarheeze\Compass\Analyzer\Composer\ComposerWarning::PROJECT_WITHOUT_LOCK)
                Project without a committed composer.lock
                @break
            @case (\Maarheeze\Compass\Analyzer\Composer\ComposerWarning::UNKNOWN_TYPE)
                composer.json type is not library or project
                @break
        @endswitch
    </span>
@endif
