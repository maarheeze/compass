@props(['isActive', 'routeName'])

<a
    href="{{ route($routeName) }}"
    @class([
        '-mb-px inline-flex items-center gap-2 border-b-2 px-2 pb-2',
        'border-orange-400 font-semibold text-foreground' => $isActive,
        'border-transparent text-foreground-muted hover:border-border hover:text-foreground' => ! $isActive,
    ])>{{ $slot }}</a>
