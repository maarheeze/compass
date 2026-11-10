<x-compass::toast
    :type="$toastType()->value"
    data-compass-toast-id="{{ $run->id }}"
    :data-compass-toast-stays-open="$staysOpen()">
    <x-compass::run-label :run="$run" />
    &middot;
    <x-compass::enum-label :value="$run->status" />
</x-compass::toast>
