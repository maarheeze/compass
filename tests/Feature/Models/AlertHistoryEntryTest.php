<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;

test('an entry is deleted with its alert', function (): void {
    $alert = Alert::factory()->create();
    $alertHistoryEntry = AlertHistoryEntry::factory()->create(['alert_id' => $alert->id]);

    $alert->delete();

    $this->assertModelMissing($alertHistoryEntry);
});
