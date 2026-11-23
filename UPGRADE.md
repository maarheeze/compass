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
