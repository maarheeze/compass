<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Observation;

test('an observation is deleted with its alert', function (): void {
    $alert = Alert::factory()->create();
    $observation = Observation::factory()->create(['alert_id' => $alert->id]);

    $alert->delete();

    $this->assertModelMissing($observation);
});
