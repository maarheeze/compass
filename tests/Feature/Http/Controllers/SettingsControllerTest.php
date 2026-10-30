<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('edit contains every alert type group', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SETTINGS_EDIT));

    $response->assertViewHas('alertTypeGroups', AlertTypeGroup::cases());
});

test('edit contains every notification channel', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SETTINGS_EDIT));

    $response->assertViewHas('notificationChannels', NotificationChannel::cases());
});

test('edit contains the notification subscriptions of the signed in user', function (): void {
    $user = User::factory()->create();
    $notificationSubscription = NotificationSubscription::factory()
        ->for($user)
        ->create();
    NotificationSubscription::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SETTINGS_EDIT));

    $response->assertViewHas(
        'notificationSubscriptions',
        static function (NotificationSubscriptionCollection $notificationSubscriptions) use ($notificationSubscription): bool {
            return $notificationSubscriptions->sole()->is($notificationSubscription);
        },
    );
});

test('update stores the chosen notification channels of the signed in user', function (): void {
    $user = User::factory()->create();
    $alertType = fake()->randomElement(AlertType::cases());
    $notificationChannel = fake()->randomElement(NotificationChannel::cases());
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::SETTINGS_UPDATE), [
        'notification_channels' => [$alertType->value => [$notificationChannel->value]],
    ]);

    $this->assertDatabaseHas(NotificationSubscription::class, [
        'user_id' => $user->id,
        'alert_type' => $alertType,
        'channel' => $notificationChannel,
    ]);
});

test('update removes the notification subscriptions of the signed in user when nothing is chosen', function (): void {
    $user = User::factory()->create();
    NotificationSubscription::factory()
        ->for($user)
        ->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::SETTINGS_UPDATE), []);

    $this->assertDatabaseMissing(NotificationSubscription::class, ['user_id' => $user->id]);
});

test('update refuses an unknown alert type', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(route(RouteName::SETTINGS_UPDATE), [
        'notification_channels' => [fake()->slug() => [fake()->randomElement(NotificationChannel::cases())->value]],
    ]);

    $response->assertSessionHasErrors('notification_channels');
});

test('update refuses an unknown notification channel', function (): void {
    $alertType = fake()->randomElement(AlertType::cases());
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(route(RouteName::SETTINGS_UPDATE), [
        'notification_channels' => [$alertType->value => [fake()->slug()]],
    ]);

    $response->assertSessionHasErrors(sprintf('notification_channels.%s.0', $alertType->value));
});

test('update refuses a notification channel chosen twice for one alert type', function (): void {
    $alertType = fake()->randomElement(AlertType::cases());
    $notificationChannel = fake()->randomElement(NotificationChannel::cases());
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(route(RouteName::SETTINGS_UPDATE), [
        'notification_channels' => [$alertType->value => [$notificationChannel->value, $notificationChannel->value]],
    ]);

    $response->assertSessionHasErrors(sprintf('notification_channels.%s.0', $alertType->value));
});

test('edit shows that the notification settings were refused', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->followingRedirects()
        ->from(route(RouteName::SETTINGS_EDIT))
        ->patch(route(RouteName::SETTINGS_UPDATE), ['notification_channels' => [fake()->slug() => []]]);

    $response->assertSeeText('Your notification settings were not saved. Please try again.');
});

test('update sends the user back to the settings', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(route(RouteName::SETTINGS_UPDATE), []);

    $response->assertRedirect(route(RouteName::SETTINGS_EDIT));
});
