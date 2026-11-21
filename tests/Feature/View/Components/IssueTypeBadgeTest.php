<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\IssueTypeBadge;

test('badge type follows the issue type', function (IssueType $issueType, BadgeType $badgeType): void {
    $issueTypeBadge = $this->app->make(IssueTypeBadge::class, ['issueType' => $issueType]);

    expect($issueTypeBadge->badgeType)
        ->toBe($badgeType);
})->with([
    'bug' => [IssueType::BUG, BadgeType::DANGER],
    'feature' => [IssueType::FEATURE, BadgeType::INFO],
    'task' => [IssueType::TASK, BadgeType::WARNING],
]);

test('render renders the issue type badge view', function (): void {
    $issueTypeBadge = $this->app->make(IssueTypeBadge::class, [
        'issueType' => fake()->randomElement(IssueType::cases()),
    ]);

    expect($issueTypeBadge->render()->name())
        ->toBe('compass::components.issue-type-badge');
});
