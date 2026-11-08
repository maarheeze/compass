<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Alerts\Types\RepositoryMissing;

test('alert type is the registered alert type with the identifier', function (): void {
    $repositoryMissing = new RepositoryMissing();
    $alertTypes = new AlertTypes();
    $alertTypes->register($repositoryMissing);

    $alertType = $alertTypes->alertType($repositoryMissing->identifier());

    expect($alertType)
        ->toBe($repositoryMissing);
});

test('alert type refuses an identifier no registered alert type has', function (): void {
    $alertTypes = new AlertTypes();

    $this->expectException(InvalidArgumentException::class);

    $alertTypes->alertType(fake()->slug());
});

test('identifiers are the identifiers of the registered alert types', function (): void {
    $repositoryMissing = new RepositoryMissing();
    $alertTypes = new AlertTypes();
    $alertTypes->register($repositoryMissing);

    $identifiers = $alertTypes->identifiers();

    expect($identifiers)
        ->toBe([$repositoryMissing->identifier()]);
});
