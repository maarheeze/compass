@props(['missingAt', 'missingIgnoredAt'])

@if ($missingIgnoredAt)
    <span class="rounded-md bg-border/50 px-2 py-0.5 text-xs font-medium text-foreground-muted ring-1 ring-border">Ignored</span>
@elseif ($missingAt)
    <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20">Missing</span>
@endif
