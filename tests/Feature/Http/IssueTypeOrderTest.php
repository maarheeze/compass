<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\IssueTypeOrder;

test('issue type order is the configured issue types in their order', function (): void {
    $configuredIssueTypes = fake()->shuffle(IssueType::cases());
    $this->setConfiguration('compass.issue_type_order', $configuredIssueTypes);

    $issueTypeOrder = $this->app->make(IssueTypeOrder::class);

    expect($issueTypeOrder->issueTypes)
        ->toBe($configuredIssueTypes);
});

test('issue type order refuses a configured value that is not an issue type', function (): void {
    $this->setConfiguration('compass.issue_type_order', [fake()->word()]);

    $this->app->make(IssueTypeOrder::class);
})->throws(InvalidArgumentException::class);
