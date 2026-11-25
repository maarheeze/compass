<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\Forge;

use Maarheeze\Compass\Collections\CollectedSiteDomainCollection;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SiteDomains\CollectedSiteDomain;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainType;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

use function sprintf;

readonly class SiteDomainsQuery
{
    public function __construct(
        private ForgeClient $forgeClient,
        private string $organisation,
    ) {
    }

    /**
     * @throws ForgeRequestFailed
     * @throws RequestBudgetExhausted
     */
    public function siteDomains(Site $site): CollectedSiteDomainCollection
    {
        $collectedSiteDomains = new CollectedSiteDomainCollection();
        $cursor = null;

        do {
            $siteDomainsResponse = $this->getSiteDomainsResponse($site, $cursor);

            foreach (Assert::isList($siteDomainsResponse['data']) as $siteDomainResource) {
                $collectedSiteDomains->push($this->getCollectedSiteDomain(Assert::isArray($siteDomainResource)));
            }

            $cursor = Assert::nullOrString(Assert::isArray($siteDomainsResponse['meta'])['next_cursor']);
        } while ($cursor !== null);

        return $collectedSiteDomains;
    }

    /**
     * @param array<mixed> $siteDomainResource
     */
    private function getCollectedSiteDomain(array $siteDomainResource): CollectedSiteDomain
    {
        $siteDomainAttributes = Assert::isArray($siteDomainResource['attributes']);

        return new CollectedSiteDomain(
            Assert::string($siteDomainAttributes['name']),
            SiteDomainType::from(Assert::string($siteDomainAttributes['type'])),
            SiteDomainStatus::from(Assert::string($siteDomainAttributes['status'])),
        );
    }

    /**
     * @return array<mixed>
     *
     * @throws ForgeRequestFailed
     * @throws RequestBudgetExhausted
     */
    private function getSiteDomainsResponse(Site $site, ?string $cursor): array
    {
        $path = sprintf(
            'orgs/%s/servers/%s/sites/%s/domains',
            $this->organisation,
            $site->server_external_id,
            $site->external_id,
        );
        $response = $this->forgeClient->get($path, [
            'page[size]' => 100,
            'page[cursor]' => $cursor,
        ]);

        return Assert::isArray($response->json());
    }
}
