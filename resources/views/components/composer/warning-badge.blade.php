@if ($composerWarning)
    <x-compass::badge type="warning">
        <x-compass::enum-label :value="$composerWarning" />
    </x-compass::badge>
@endif
