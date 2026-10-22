<x-compass::layouts.app :title="__('Sign in')">
    <main class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-sm space-y-6 rounded-md border border-border bg-canvas p-8">
            <div class="space-y-1">
                <h1 class="text-lg font-semibold text-foreground">{{ config('app.name') }}</h1>
                <p class="text-sm text-foreground-muted">Repository and deployment status at a glance.</p>
            </div>

            @error('github')
                <p class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-950 dark:text-red-300">{{ $message }}</p>
            @enderror

            <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REDIRECT) }}"
               class="flex w-full items-center justify-center gap-2 rounded-md bg-green-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-800 dark:bg-green-600 dark:hover:bg-green-700">
                Continue with GitHub
            </a>

            <p class="text-xs text-foreground-muted">
                Compass asks for the <code>read:org</code> and <code>user:email</code> scopes to check that you are a
                member of the organization.
            </p>
        </div>
    </main>
</x-compass::layouts.app>
