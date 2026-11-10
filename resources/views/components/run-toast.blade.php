<x-compass::toast :type="$toastType()->value" data-run-id="{{ $run->id }}">
    <x-compass::run-label :run="$run" />
    &middot;
    <x-compass::enum-label :value="$run->status" />
</x-compass::toast>
