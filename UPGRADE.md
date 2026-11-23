# Upgrade guide

What to change when upgrading to the next major version, newest first.

## 1.x to 2.0

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
