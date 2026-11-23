<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Throwable;

use function in_array;

readonly class ExceptionMessages
{
    /**
     * @return list<string>
     */
    public function messages(Throwable $exception): array
    {
        $messages = [];
        $chainedException = $exception;

        while ($chainedException instanceof Throwable) {
            $message = $chainedException->getMessage();

            if ($message !== '' && !in_array($message, $messages, true)) {
                $messages[] = $message;
            }

            $chainedException = $chainedException->getPrevious();
        }

        return $messages;
    }
}
