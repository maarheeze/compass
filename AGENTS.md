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
