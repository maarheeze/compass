<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function base64_decode;
use function base64_encode;
use function rtrim;
use function strtr;

readonly class EndpointPathRouteParameter
{
    public function decode(string $routeParameter): EndpointPath
    {
        $path = base64_decode(strtr($routeParameter, '-_', '+/'), true);

        if ($path === false) {
            throw new NotFoundHttpException();
        }

        return new EndpointPath($path);
    }

    public function encode(EndpointPath $endpointPath): string
    {
        return rtrim(strtr(base64_encode($endpointPath->toString()), '+/', '-_'), '=');
    }
}
