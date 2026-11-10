<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\AssigneeFilter;

test('route parameters hold the source and the login of the assignee', function (): void {
    $source = fake()->slug();
    $login = fake()->userName();

    $assigneeFilter = new AssigneeFilter($source, $login);
    $routeParameters = $assigneeFilter->routeParameters();

    expect($routeParameters)
        ->toBe([
            AssigneeFilter::SOURCE_PARAMETER => $source,
            AssigneeFilter::ASSIGNEE_PARAMETER => $login,
        ]);
});
