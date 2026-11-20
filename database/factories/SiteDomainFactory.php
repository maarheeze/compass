<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainType;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteDomain;

use function fake;

/**
 * @extends Factory<SiteDomain>
 */
class SiteDomainFactory extends Factory
{
    /** @var class-string<SiteDomain> */
    protected $model = SiteDomain::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => fake()->unique()->domainName(),
            'type' => fake()->randomElement(SiteDomainType::cases()),
            'status' => fake()->randomElement(SiteDomainStatus::cases()),
        ];
    }
}
