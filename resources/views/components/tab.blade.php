@props(['activeRouteName', 'routeName'])

<a
    href="{{ route($routeName) }}"
    @class([
        '-mb-px border-b-2 pb-2',
        'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' => $activeRouteName === $routeName,
        'border-transparent text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeRouteName !== $routeName,
    ])>{{ $slot }}</a>
