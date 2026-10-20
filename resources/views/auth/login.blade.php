<x-compass::layouts.app :title="__('Sign in')">
    <main class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-sm space-y-6 rounded-xl bg-white p-8 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
            <div class="space-y-1">
                <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ config('app.name') }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Repository and deployment status at a glance.</p>
            </div>

            @error('github')
                <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-950 dark:text-red-300">{{ $message }}</p>
            @enderror

            <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REDIRECT) }}"
               class="flex w-full items-center justify-center gap-2 rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                Continue with GitHub
            </a>

            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                Compass asks for the <code>read:org</code> and <code>user:email</code> scopes to check that you are a
                member of the organisation.
            </p>
        </div>
    </main>
</x-compass::layouts.app>
