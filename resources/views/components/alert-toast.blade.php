<x-compass::toast :type="$toastType()->value">
    <x-compass::enum-label :value="$alertHistoryEntry->type" />
    &middot;
    <x-compass::alert-type-label :identifier="$alertHistoryEntry->alert->alert_type_identifier" />
    &middot;
    <x-compass::alert-subject-name :subject="$alertHistoryEntry->alert->subject" />
</x-compass::toast>
