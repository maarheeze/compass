<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function base64_decode;
use function base64_encode;
use function rtrim;
use function strtr;

readonly class EndpointPath
{
    public function __construct(
        public string $path,
    ) {
    }

    public static function fromRouteKey(string $routeKey): self
    {
        $path = base64_decode(strtr($routeKey, '-_', '+/'), true);

        if ($path === false) {
            throw new NotFoundHttpException();
        }

        return new self($path);
    }

    public function routeKey(): string
    {
        return rtrim(strtr(base64_encode($this->path), '+/', '-_'), '=');
    }
}
