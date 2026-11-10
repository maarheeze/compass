<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Webmozart\Assert\Assert;

/**
 * @implements CastsAttributes<EndpointPath, EndpointPath>
 */
class EndpointPathCast implements CastsAttributes
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): EndpointPath
    {
        Assert::string($value);

        return new EndpointPath($value);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        Assert::isInstanceOf($value, EndpointPath::class);

        return $value->toString();
    }
}
