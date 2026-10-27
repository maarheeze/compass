<x-compass::layouts.app :title="__('compass::authentication.sign_in')">
    <main class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-sm space-y-6 rounded-md border border-border bg-canvas p-8">
            <div class="space-y-1">
                <h1 class="text-lg font-semibold text-foreground"><x-compass::application-name /></h1>
                <p class="text-sm text-foreground-muted">{{ __('compass::authentication.tagline') }}</p>
            </div>

            @error('github')
                <p class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-950 dark:text-red-300">{{ $message }}</p>
            @enderror

            <x-compass::git-hub-sign-in-link />

            <p class="text-xs text-foreground-muted">
                {!! __('compass::authentication.scopes', [
                    'read_org' => '<code>read:org</code>',
                    'user_email' => '<code>user:email</code>',
                ]) !!}
            </p>
        </div>
    </main>
</x-compass::layouts.app>
