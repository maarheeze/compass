@props(['aside'])

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_16rem]">
    <div class="min-w-0 space-y-6">
        {{ $slot }}
    </div>

    <aside class="space-y-4 text-sm">
        {{ $aside }}
    </aside>
</div>
