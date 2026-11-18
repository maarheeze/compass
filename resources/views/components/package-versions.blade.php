<section class="space-y-3">
    <x-compass::section-heading>
        <x-slot:icon>
            <x-compass::icons.package />
        </x-slot:icon>
        {{ __('compass::package.packages') }}
    </x-compass::section-heading>
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($packageNames as $packageName)
            <x-compass::package-versions-table :package-name="$packageName" />
        @endforeach
    </div>
</section>
