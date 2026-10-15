<x-compass::layouts.app :title="__('Sign in')">
    <main class="flex min-h-screen items-center justify-center bg-zinc-50 p-6">
        <div class="w-full max-w-sm space-y-6 rounded-xl bg-white p-8 shadow-sm ring-1 ring-zinc-950/5">
            <div class="space-y-1">
                <h1 class="text-lg font-semibold text-zinc-900">{{ config('app.name') }}</h1>
                <p class="text-sm text-zinc-500">Repository and deployment status at a glance.</p>
            </div>

            <a href="{{ route('compass.auth.github') }}"
               class="flex w-full items-center justify-center gap-2 rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700">
                Continue with GitHub
            </a>

            <p class="text-xs text-zinc-500">
                Compass asks for the <code>read:org</code> and <code>user:email</code> scopes to check that you are a
                member of an allowed organisation.
            </p>
        </div>
    </main>
</x-compass::layouts.app>
