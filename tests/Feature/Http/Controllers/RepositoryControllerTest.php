<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Types\ComposerLibraryWithLock;
use Maarheeze\Compass\Alerts\Types\ComposerProjectWithoutLock;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Http\RepositoryFilter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerJsonPackage;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Filter;
use Maarheeze\Compass\View\FilterOption;

test('index contains the repositories', function (): void {
    $repository = Repository::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES));

    $response->assertViewHas('repositories', static function (LengthAwarePaginator $viewRepositories) use ($repository): bool {
        return $viewRepositories->sole()->is($repository);
    });
});

test('index counts the issues of the repositories', function (): void {
    $repository = Repository::factory()->create();
    Issue::factory()->create(['repository_id' => $repository->id]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES));

    $response->assertViewHas('repositories', static function (LengthAwarePaginator $viewRepositories): bool {
        return $viewRepositories->sole()->issues_count === 1;
    });
});

test('index counts the pull requests of the repositories', function (): void {
    $repository = Repository::factory()->create();
    PullRequest::factory()->create(['repository_id' => $repository->id]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES));

    $response->assertViewHas('repositories', static function (LengthAwarePaginator $viewRepositories): bool {
        return $viewRepositories->sole()->pull_requests_count === 1;
    });
});

test('index on a kind contains only the repositories of that kind', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);
    Repository::factory()->create(['composer_json_version' => null]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES, [RepositoryFilter::KIND_PARAMETER => RepositoryKind::LIBRARY->value]));

    $response->assertViewHas('repositories', static function (LengthAwarePaginator $viewRepositories) use ($repository): bool {
        return $viewRepositories->sole()->is($repository);
    });
});

test('index on a visibility contains only the repositories of that visibility', function (): void {
    $repository = Repository::factory()->create(['visibility' => RepositoryVisibility::PRIVATE]);
    Repository::factory()->create(['visibility' => RepositoryVisibility::PUBLIC]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES, [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PRIVATE->value]));

    $response->assertViewHas('repositories', static function (LengthAwarePaginator $viewRepositories) use ($repository): bool {
        return $viewRepositories->sole()->is($repository);
    });
});

test('index shows the configured number of repositories per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);
    $this->setConfiguration('compass.items_per_page', $itemsPerPage);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES));

    $response->assertViewHas('repositories', static function (LengthAwarePaginator $viewRepositories) use ($itemsPerPage): bool {
        return $viewRepositories->perPage() === $itemsPerPage;
    });
});

test('index on a kind keeps the kind on the other pages', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES, [RepositoryFilter::KIND_PARAMETER => RepositoryKind::OTHER->value]));

    $response->assertViewHas('repositories', static function (LengthAwarePaginator $viewRepositories): bool {
        return $viewRepositories->url(2) === route(RouteName::REPOSITORIES, [
            RepositoryFilter::KIND_PARAMETER => RepositoryKind::OTHER->value,
            'page' => 2,
        ]);
    });
});

test('index contains the kind filter of the repository filter', function (): void {
    Repository::factory()->create(['composer_json_version' => null]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES, [RepositoryFilter::KIND_PARAMETER => RepositoryKind::PROJECT->value]));

    $response->assertViewHas('kindFilter', static function (Filter $viewKindFilter): bool {
        $otherOption = $viewKindFilter->options->firstOrFail(static function (FilterOption $filterOption): bool {
            return $filterOption->queryParameters === [RepositoryFilter::KIND_PARAMETER => RepositoryKind::OTHER->value];
        });

        return $otherOption->count === 1;
    });
});

test('index contains the visibility filter of the repository filter', function (): void {
    Repository::factory()->create(['visibility' => RepositoryVisibility::PUBLIC]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES, [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PRIVATE->value]));

    $response->assertViewHas('visibilityFilter', static function (Filter $viewVisibilityFilter): bool {
        $publicOption = $viewVisibilityFilter->options->firstOrFail(static function (FilterOption $filterOption): bool {
            return $filterOption->queryParameters === [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PUBLIC->value];
        });

        return $publicOption->count === 1;
    });
});

test('index refuses an unknown kind', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES, [RepositoryFilter::KIND_PARAMETER => fake()->word()]));

    $response->assertInvalid(RepositoryFilter::KIND_PARAMETER);
});

test('index refuses an unknown visibility', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORIES, [RepositoryFilter::VISIBILITY_PARAMETER => fake()->word()]));

    $response->assertInvalid(RepositoryFilter::VISIBILITY_PARAMETER);
});

test('show contains the repository', function (): void {
    $repository = Repository::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $repository));

    $response->assertViewHas('repository', static function (Repository $viewRepository) use ($repository): bool {
        return $viewRepository->is($repository);
    });
});

test('show contains the kind of the repository', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $repository));

    $response->assertViewHas('kind', RepositoryKind::LIBRARY);
});

test('show contains the alerts of the repository newest first', function (): void {
    $repository = Repository::factory()->create();
    $earlierAlert = Alert::factory()
        ->done()
        ->composerProjectWithoutLock()
        ->for($repository, 'subject')
        ->create(['created_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $laterAlert = Alert::factory()
        ->active()
        ->composerProjectWithoutLock()
        ->for($repository, 'subject')
        ->create(['created_at' => fake()->dateTimeBetween('-1 month')]);
    Alert::factory()
        ->composerProjectWithoutLock()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $repository));

    $response->assertViewHas('alerts', static function (AlertCollection $viewAlerts) use ($earlierAlert, $laterAlert): bool {
        return $viewAlerts->count() === 2
            && $viewAlerts->first()->id->equals($laterAlert->id)
            && $viewAlerts->last()->id->equals($earlierAlert->id);
    });
});

test('show loads the assignee of the alerts of the repository', function (): void {
    $alert = Alert::factory()
        ->composerProjectWithoutLock()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $alert->subject_id));

    $response->assertViewHas('alerts', static function (AlertCollection $viewAlerts): bool {
        return $viewAlerts->sole()->relationLoaded('assignee');
    });
});

test('show contains the checks of the repository that apply', function (): void {
    $repository = Repository::factory()->create();
    $applicableAlertCheck = AlertCheck::factory()
        ->for($repository, 'subject')
        ->create([
            'alert_type_identifier' => new ComposerLibraryWithLock()->identifier(),
            'condition' => fake()->randomElement([ConditionState::ACTIVE, ConditionState::RESOLVED]),
        ]);
    AlertCheck::factory()
        ->for($repository, 'subject')
        ->create([
            'alert_type_identifier' => new ComposerProjectWithoutLock()->identifier(),
            'condition' => ConditionState::NOT_APPLICABLE,
        ]);
    AlertCheck::factory()
        ->for(Repository::factory(), 'subject')
        ->create(['condition' => ConditionState::ACTIVE]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $repository));

    $response->assertViewHas(
        'alertChecks',
        static function (AlertCheckCollection $viewAlertChecks) use ($applicableAlertCheck): bool {
            return $viewAlertChecks->sole()->is($applicableAlertCheck);
        },
    );
});

test('show loads whether the alerts of the checks of the repository are muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->composerLibraryWithLock()
        ->create();
    AlertCheck::factory()
        ->for($alert->subject, 'subject')
        ->create([
            'alert_type_identifier' => $alert->alert_type_identifier,
            'condition' => ConditionState::ACTIVE,
        ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $alert->subject_id));

    $response->assertViewHas('alertChecks', static function (AlertCheckCollection $viewAlertChecks): bool {
        return $viewAlertChecks->sole()->alert_is_muted === true;
    });
});

test('show contains the issues of the repository newest first', function (): void {
    $repository = Repository::factory()->create();
    $olderIssue = Issue::factory()->create([
        'repository_id' => $repository->id,
        'opened_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $newerIssue = Issue::factory()->create([
        'repository_id' => $repository->id,
        'opened_at' => fake()->dateTimeBetween('-1 month'),
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $repository));

    $response->assertViewHas('issues', static function (IssueCollection $viewIssues) use ($newerIssue, $olderIssue): bool {
        return $viewIssues->first()->id->equals($newerIssue->id)
            && $viewIssues->last()->id->equals($olderIssue->id);
    });
});

test('show contains the pull requests of the repository newest first', function (): void {
    $repository = Repository::factory()->create();
    $olderPullRequest = PullRequest::factory()->create([
        'repository_id' => $repository->id,
        'opened_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $newerPullRequest = PullRequest::factory()->create([
        'repository_id' => $repository->id,
        'opened_at' => fake()->dateTimeBetween('-1 month'),
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $repository));

    $response->assertViewHas(
        'pullRequests',
        static function (PullRequestCollection $viewPullRequests) use ($newerPullRequest, $olderPullRequest): bool {
            return $viewPullRequests->first()->id->equals($newerPullRequest->id)
                && $viewPullRequests->last()->id->equals($olderPullRequest->id);
        },
    );
});

test('show contains the production packages of the repository', function (): void {
    $productionPackage = ComposerJsonPackage::factory()->create(['section' => PackageSection::PRODUCTION]);
    ComposerJsonPackage::factory()->create([
        'composer_json_id' => $productionPackage->composer_json_id,
        'section' => PackageSection::DEVELOPMENT,
    ]);
    ComposerJsonPackage::factory()->create(['section' => PackageSection::PRODUCTION]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $productionPackage->composerJson->repository_id));

    $response->assertViewHas(
        'productionPackages',
        static function (ComposerJsonPackageCollection $viewProductionPackages) use ($productionPackage): bool {
            return $viewProductionPackages->sole()->is($productionPackage);
        },
    );
});

test('show contains the development packages of the repository', function (): void {
    $developmentPackage = ComposerJsonPackage::factory()->create(['section' => PackageSection::DEVELOPMENT]);
    ComposerJsonPackage::factory()->create([
        'composer_json_id' => $developmentPackage->composer_json_id,
        'section' => PackageSection::PRODUCTION,
    ]);
    ComposerJsonPackage::factory()->create(['section' => PackageSection::DEVELOPMENT]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $developmentPackage->composerJson->repository_id));

    $response->assertViewHas(
        'developmentPackages',
        static function (ComposerJsonPackageCollection $viewDevelopmentPackages) use ($developmentPackage): bool {
            return $viewDevelopmentPackages->sole()->is($developmentPackage);
        },
    );
});

test('show contains the locked packages of the repository', function (): void {
    $lockedPackage = ComposerLockPackage::factory()->create();
    ComposerLockPackage::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $lockedPackage->composerLock->repository_id));

    $response->assertViewHas(
        'lockedPackages',
        static function (ComposerLockPackageCollection $viewLockedPackages) use ($lockedPackage): bool {
            return $viewLockedPackages->sole()->is($lockedPackage);
        },
    );
});

test('show contains the sites of the repository', function (): void {
    $repository = Repository::factory()->create();
    $site = Site::factory()->create(['repository_url' => $repository->url]);
    Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::REPOSITORY, $repository));

    $response->assertViewHas('sites', static function (SiteCollection $viewSites) use ($site): bool {
        return $viewSites->sole()->is($site);
    });
});

test('delete deletes a missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->delete(route(RouteName::REPOSITORY_DELETE, $repository));

    $this->assertModelMissing($repository);
});

test('delete sends the user back to the dashboard', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::REPOSITORY_DELETE, $repository));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});

test('delete is not found for a collected repository', function (): void {
    $repository = Repository::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::REPOSITORY_DELETE, $repository));

    $response->assertNotFound();
});

test('ignore marks a missing repository ignored', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::REPOSITORY_IGNORE, $repository));

    expect($repository->refresh()->missing_ignored_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('ignore sends the user back to the dashboard', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::REPOSITORY_IGNORE, $repository));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});
