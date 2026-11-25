# Upgrade guide

What to change when upgrading to the next major version, newest first.

## 1.x to 2.0

### Let the queue finish before upgrading

Stop the scheduler and let the queue workers finish their jobs before you
deploy 2.0. Queued jobs are stored with their class name, and some job classes
have moved, so a job queued by 1.x fails under 2.0 and leaves its run running.

### `repository-group-list` component removed

Replace `<x-compass::repository-group-list>` with `<x-compass::repository-list>`.
The `RepositoryGroup` class is removed with it: repositories are now listed on
one paginated list instead of grouped.

### Count attributes removed from the list components

Remove `open-count` from `issue-list` and `pull-request-list`,
`repository-count` from `repository-list` and `site-count` from `site-list`.
The lists no longer show a count badge.

### `url` renamed to `source_url`

Read `source_url` instead of `url` on `Repository`, `Issue` and `PullRequest`,
and allow for null.

### `number` replaced by `external_reference`

Read `external_reference` instead of `number` on `Issue` and `PullRequest`. It
holds the reference as the provider writes it, such as `owner/repository#123`,
and is filled at the next collect: until then it holds the bare number. The
`issue-reference` component takes `external-reference` instead of `number`.

### `DISPLAY_ORDER` replaced by `displayOrder()`

Call `RepositoryKind::displayOrder()` and `RepositoryVisibility::displayOrder()`
instead of reading their `DISPLAY_ORDER` constant.

### Translation keys removed

If you override the package's translations, remove these keys, which are no
longer used:

- `issue.open_count`
- `pull-request.open_count`
- `repository.libraries`, `repository.other`, `repository.projects` and
  `repository.repository_count`
- `site.site_count`

### `issue_types` added to the `github` source

To give issues a type, add
`'issue_types' => ['Bug' => IssueType::BUG, 'Feature' => IssueType::FEATURE, 'Task' => IssueType::TASK]`
to a `github` source, mapping the names of your organisation's issue types onto
Compass's. Left out, no issue has a type, and `issue_type_order` has nothing to
order by.

### `issue_type_order` added

To show some issue types first on the issue lists, in that order, add
`'issue_type_order' => [IssueType::BUG, IssueType::FEATURE]` to your published
`config/compass.php`. Left out, it is empty and every list shows the oldest
opened issue first.

### High and low queues added

Runs now go on a queue by priority. To have a run started by hand, or asked
for by a site, go first, set `COMPASS_HIGH_QUEUE` and `COMPASS_LOW_QUEUE` and
start the worker with the high queue before the default queue and the low
queue after it, as in `--queue=high,default,low`. Left unset, every run stays
on the default queue.

### `requests_per_minute` added to the `github` and `forge` sources

Add `'requests_per_minute' => env('COMPASS_GITHUB_REQUESTS_PER_MINUTE', 60)` to a
`github` source and `'requests_per_minute' => env('COMPASS_FORGE_REQUESTS_PER_MINUTE', 60)`
to a `forge` source. Compass sends at most that many requests a minute to the
source, and a job that would go over waits instead of failing. A source without
it cannot be collected.

### Collector managers replaced by `SourceDrivers`

If you added a provider, register its driver once with
`SourceDrivers::extend('gitlab', fn (Container $container, SourceConfig $sourceConfig) => new GitLabDriver(...))`
instead of extending a `*CollectorManager` per kind; the managers are removed.
The driver is one class implementing every collector contract the provider
can deliver, such as `RepositoriesCollector` and `IssuesCollector`. The
built-in collectors are replaced by `GitHubDriver`, `ForgeDriver` and
`CompassClientDriver`.
