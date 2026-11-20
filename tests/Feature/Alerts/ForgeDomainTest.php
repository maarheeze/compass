<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\ForgeDomain;

test('covers a hostname on the forge domain', function (): void {
    $forgeDomain = new ForgeDomain();

    $coversHostname = $forgeDomain->covers(sprintf('%s.on-forge.com', fake()->slug()));

    expect($coversHostname)
        ->toBeTrue();
});

test('covers no hostname on another domain', function (): void {
    $forgeDomain = new ForgeDomain();

    $coversHostname = $forgeDomain->covers(fake()->domainName());

    expect($coversHostname)
        ->toBeFalse();
});
