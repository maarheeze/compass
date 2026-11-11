<x-compass::toast :type="$toastType()->value">
    <x-compass::enum-label :labelled-enum="$alertHistoryEntry->type" />
    &middot;
    <x-compass::alert-type-label :identifier="$alertHistoryEntry->alert->alert_type_identifier" />
    &middot;
    <x-compass::alert-subject-name :alert-subject="$alertHistoryEntry->alert->subject" />
</x-compass::toast>
