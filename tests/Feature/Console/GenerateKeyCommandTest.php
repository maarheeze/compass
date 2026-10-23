<?php

declare(strict_types=1);

use Maarheeze\Compass\Console\GenerateKeyCommand;

test('handle adds a key to the environment file', function (): void {
    $environmentPath = sprintf('%s/%s', sys_get_temp_dir(), fake()->uuid());
    mkdir($environmentPath);
    file_put_contents(sprintf('%s/.env', $environmentPath), sprintf("APP_NAME=%s\n", fake()->word()));
    $this->app->useEnvironmentPath($environmentPath);
    $this->setConfiguration('compass.client.key', null);

    $this->artisan(GenerateKeyCommand::class)->assertSuccessful();

    expect(file_get_contents(sprintf('%s/.env', $environmentPath)))
        ->toMatch('/^COMPASS_CLIENT_KEY=[0-9a-f]{64}$/m');
});

test('handle fills an empty key in the environment file', function (): void {
    $environmentPath = sprintf('%s/%s', sys_get_temp_dir(), fake()->uuid());
    mkdir($environmentPath);
    file_put_contents(sprintf('%s/.env', $environmentPath), "COMPASS_CLIENT_KEY=\n");
    $this->app->useEnvironmentPath($environmentPath);
    $this->setConfiguration('compass.client.key', '');

    $this->artisan(GenerateKeyCommand::class)->assertSuccessful();

    expect(file_get_contents(sprintf('%s/.env', $environmentPath)))
        ->toMatch('/\ACOMPASS_CLIENT_KEY=[0-9a-f]{64}\n\z/');
});

test('handle refuses to replace a key that is already set', function (): void {
    $environment = sprintf("COMPASS_CLIENT_KEY=%s\n", fake()->sha1());
    $environmentPath = sprintf('%s/%s', sys_get_temp_dir(), fake()->uuid());
    mkdir($environmentPath);
    file_put_contents(sprintf('%s/.env', $environmentPath), $environment);
    $this->app->useEnvironmentPath($environmentPath);

    $this->artisan(GenerateKeyCommand::class)->assertFailed();

    expect(file_get_contents(sprintf('%s/.env', $environmentPath)))
        ->toBe($environment);
});

test('handle replaces a key that is already set when forced', function (): void {
    $key = fake()->sha1();
    $environmentPath = sprintf('%s/%s', sys_get_temp_dir(), fake()->uuid());
    mkdir($environmentPath);
    file_put_contents(sprintf('%s/.env', $environmentPath), sprintf("COMPASS_CLIENT_KEY=%s\n", $key));
    $this->app->useEnvironmentPath($environmentPath);
    $this->setConfiguration('compass.client.key', $key);

    $this->artisan(GenerateKeyCommand::class, ['--force' => true])->assertSuccessful();

    expect(file_get_contents(sprintf('%s/.env', $environmentPath)))
        ->toMatch('/\ACOMPASS_CLIENT_KEY=[0-9a-f]{64}\n\z/');
});

test('handle only shows a key when asked to', function (): void {
    $environment = sprintf("APP_NAME=%s\n", fake()->word());
    $environmentPath = sprintf('%s/%s', sys_get_temp_dir(), fake()->uuid());
    mkdir($environmentPath);
    file_put_contents(sprintf('%s/.env', $environmentPath), $environment);
    $this->app->useEnvironmentPath($environmentPath);

    $this->artisan(GenerateKeyCommand::class, ['--show' => true])->assertSuccessful();

    expect(file_get_contents(sprintf('%s/.env', $environmentPath)))
        ->toBe($environment);
});
