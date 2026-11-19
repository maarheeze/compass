<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;

test('route parameters hold the source and the login of the assignee', function (): void {
    $source = fake()->slug();
    $login = fake()->userName();
    $assignee = new Assignee($source, $login);

    $routeParameters = $assignee->routeParameters();

    expect($routeParameters)
        ->toBe([
            AssigneeFilter::SOURCE_PARAMETER => $source,
            AssigneeFilter::ASSIGNEE_PARAMETER => $login,
        ]);
});
