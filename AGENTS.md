=== project ===

# Compass

A self-hosted, read-only status dashboard for the GitHub repositories of one organisation and the Laravel Forge sites they deploy to, shipped as the Laravel package `maarheeze/compass`.

## Problem

Our code lives in GitHub repositories and is deployed through Laravel Forge. There is no single place that shows, per repository, what the latest commit is, what is actually deployed, whether that deployment succeeded, what the project depends on, and which issues and pull requests are open. Answering those questions means clicking through GitHub and Forge one repository at a time.

## Goal

A web application that periodically collects status data from GitHub and Forge, stores it in a database, and shows it to people who sign in with their GitHub account.

## Distribution

Each organisation runs its own installation: a standard Laravel app that requires `maarheeze/compass` and publishes `config/compass.php`. This repository is the package, not an app. It is tested with Orchestra Testbench.

The package never touches the host's `users` table or default guard: it brings its own `compass_users` table, `compass` guard and middleware, and registers routes (named `compass.*`) under the `compass.path` prefix.

## Core principles

- **Collected data is a cache, not a source of truth.** GitHub and Forge stay authoritative; the database is refreshed periodically. Issues and pull requests a sync no longer returns are deleted; repositories and sites are marked missing instead and shown on the dashboard, because a sync that silently returns too little must not take their settings with it. Only the user deletes them, or ignores the missing mark; a row that comes back has its marks cleared.
- **Open source by design.** No hardcoded tokens or org-specific values in code. The README documents what is required.
- **A setting's default lives in the host's config, never as a fallback in code.** A setting with a value to choose is required, and its default is written where the host sees it: `config/compass.php`, or, for a source's settings, the README's example of that source as a plain value (`'requests_per_minute' => 60`), since how a host fills in its sources, from the environment or otherwise, is the host's own choice. A fallback in code is a decision the host cannot see. Settings whose absence means "none", such as a source's `issue_types` or `schedule`, are not defaults and stay optional.
- **A job per request that cannot be combined.** A collector hands over what one request to its provider returns, such as a page of repositories or sites, as one batch in one job. Where every subject needs a request of its own, such as a site's report, an endpoint check or a composer file, the requests cannot be combined: the collector takes one subject, and a job per subject calls it, so a slow or failing subject only holds up or fails its own job.

## Tech stack

- **Laravel 13 package** (namespace `Maarheeze\Compass`) with Blade
- **Orchestra Testbench** for package tests
- **Laravel Socialite** (GitHub provider) for sign-in
- **GitHub API** (GraphQL where it saves round-trips, REST where simpler) and the **Forge API**
- **Laravel scheduler + queue** for the collector

## Non-goals

- Not a replacement for any org's Project board.
- Not real-time — periodic polling is enough.
- Read-only — Compass never changes anything on GitHub or Forge: no write actions on issues or pull requests, no triggering deployments.

## Working name

**Compass** — deliberately not GitHub-branded (see GitHub's trademark/naming guidance, which discourages names implying GitHub endorsement), and broad enough to cover repositories, deployments, issues and pull requests without a rename.

=== foundation rules ===

# Guidelines

## Foundational Context

This repository is a Laravel package (`maarheeze/compass`, namespace `Maarheeze\Compass`) for Laravel 13 on PHP 8.5. It is not an application: there is no `artisan`, no `app/` and no build step. It is installed into a host Laravel application and tested with Orchestra Testbench. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version in `composer.json` and `vendor/<vendor>/<package>/composer.json`.

## Conventions

- You must follow all existing code conventions used in this package. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Package Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the package's dependencies without approval.
- Never touch the host application's `users` table, default guard or routes: Compass uses its own `compass_users` table, `compass` guard and middleware, and routes named `compass.*` under the `compass.path` prefix.

## Frontend

- Views use the host application's Vite build and Tailwind. If a frontend change is not visible, the host needs a rebuild — ask the user.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.

=== laravel/core rules ===

# Do Things the Laravel Way

- There is no `artisan` in a package: create migrations, models, factories, controllers and tests by hand, modelled on their siblings.
- Register everything (config, routes, views, migrations) in `CompassServiceProvider`; anything a host should change goes in `config/compass.php`.

### Model Creation

- When creating new models, create useful factories for them too, linked with a `newFactory()` method on the model and a `$model` property on the factory. Do not use model or factory attributes (`#[Fillable]`, `#[Hidden]`, `#[Table]`, `#[UseFactory]`, `#[UseModel]`); use the matching properties and methods.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: use `fake()`, following existing factories.

=== pest/core rules ===

# Pest

- This package uses Pest. Feature tests live in `tests/Feature` and are bound to `Maarheeze\Compass\Tests\TestCase` and `RefreshDatabase` in `tests/Pest.php`.
- Write tests as `test('...', function (): void { ... })` closures with `expect()`.
- Do not delete tests or test files without approval. They are part of the package.

=== maarheeze/guidelines rules ===

# Agent Behavior Guidelines

Read `vendor/maarheeze/guidelines/.ai/guidelines/always/behaviour.md` at the start of every task and follow it throughout.

# Guidelines from installed packages

Read these when you are about to touch a matching path — before creating or editing the file, not after.

These packages ship their own index — read it first, then the file it points to:

- maarheeze/guidelines: vendor/maarheeze/guidelines/.ai/guidelines/index.md
