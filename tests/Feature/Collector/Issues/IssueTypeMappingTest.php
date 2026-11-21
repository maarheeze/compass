<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Collector\Issues\IssueTypeMapping;
use Maarheeze\Compass\Collector\SourceConfig;

test('issue type is the compass type the issue types of the source map the name onto', function (): void {
    $issueTypeName = fake()->word();
    $issueType = fake()->randomElement(IssueType::cases());
    $issueTypeMapping = new IssueTypeMapping(new SourceConfig(fake()->slug(), [
        'issue_types' => [$issueTypeName => $issueType],
    ]));

    $mappedIssueType = $issueTypeMapping->issueType($issueTypeName);

    expect($mappedIssueType)
        ->toBe($issueType);
});

test('issue type is null for a name the issue types of the source do not map', function (): void {
    $issueTypeMapping = new IssueTypeMapping(new SourceConfig(fake()->slug(), [
        'issue_types' => [fake()->unique()->word() => fake()->randomElement(IssueType::cases())],
    ]));

    $mappedIssueType = $issueTypeMapping->issueType(fake()->unique()->word());

    expect($mappedIssueType)
        ->toBeNull();
});

test('issue type is null for an issue without a type', function (): void {
    $issueTypeMapping = new IssueTypeMapping(new SourceConfig(fake()->slug(), [
        'issue_types' => [fake()->word() => fake()->randomElement(IssueType::cases())],
    ]));

    $mappedIssueType = $issueTypeMapping->issueType(null);

    expect($mappedIssueType)
        ->toBeNull();
});

test('issue type is null for every name of a source without issue types', function (): void {
    $issueTypeMapping = new IssueTypeMapping(new SourceConfig(fake()->slug(), []));

    $mappedIssueType = $issueTypeMapping->issueType(fake()->word());

    expect($mappedIssueType)
        ->toBeNull();
});

test('issue type mapping refuses a value that is not an issue type', function (): void {
    new IssueTypeMapping(new SourceConfig(fake()->slug(), [
        'issue_types' => [fake()->word() => fake()->word()],
    ]));
})->throws(InvalidArgumentException::class);
