<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\ExceptionMessages;

test('messages holds the message of the exception and of the exceptions behind it', function (): void {
    $previousException = new RuntimeException(fake()->sentence());
    $exception = new LogicException(fake()->sentence(), 0, $previousException);

    $exceptionMessages = $this->app->make(ExceptionMessages::class);
    $messages = $exceptionMessages->messages($exception);

    expect($messages)
        ->toBe([$exception->getMessage(), $previousException->getMessage()]);
});

test('messages leaves an empty message out', function (): void {
    $previousException = new RuntimeException(fake()->sentence());

    $exceptionMessages = $this->app->make(ExceptionMessages::class);
    $messages = $exceptionMessages->messages(new LogicException('', 0, $previousException));

    expect($messages)
        ->toBe([$previousException->getMessage()]);
});

test('messages leaves a repeated message out', function (): void {
    $exception = new LogicException(fake()->sentence());

    $exceptionMessages = $this->app->make(ExceptionMessages::class);
    $messages = $exceptionMessages->messages(new RuntimeException($exception->getMessage(), 0, $exception));

    expect($messages)
        ->toBe([$exception->getMessage()]);
});
