<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\ForgeSiteCollection;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function sprintf;

readonly class SitesQuery
{
    public function __construct(
        private ForgeClient $forgeClient,
        private string $organization,
    ) {
    }

    /**
     * @throws ForgeRequestFailed
     */
    public function page(?string $cursor): SitesQueryResult
    {
        $response = $this->forgeClient->get(sprintf('orgs/%s/sites', $this->organization), [
            'include' => 'server,latestDeployment',
            'page[size]' => 100,
            'page[cursor]' => $cursor,
        ]);
        $sitesResponse = Assert::isArray($response->json());
        $includedResources = $this->getIncludedResources($sitesResponse);

        return new SitesQueryResult(
            $this->getForgeSites(Assert::isList($sitesResponse['data']), $includedResources),
            $this->getNextCursor(Assert::isArray($sitesResponse['meta'])),
        );
    }

    private function getEndedAt(mixed $endedAtResponse): ?CarbonImmutable
    {
        if ($endedAtResponse === null) {
            return null;
        }

        return CarbonImmutable::parse(Assert::string($endedAtResponse));
    }

    /**
     * @param array<string, array<mixed>> $includedResources
     */
    private function getForgeDeployment(mixed $deploymentIdentifier, array $includedResources): ?ForgeDeployment
    {
        if ($deploymentIdentifier === null) {
            return null;
        }

        $deploymentAttributes = $this->getIncludedAttributes($deploymentIdentifier, $includedResources);
        $commitAttributes = Assert::isArray($deploymentAttributes['commit']);

        return new ForgeDeployment(
            DeploymentStatus::from(Assert::string($deploymentAttributes['status'])),
            Assert::nullOrString($commitAttributes['hash']),
            Assert::nullOrString($commitAttributes['message']),
            CarbonImmutable::parse(Assert::string($deploymentAttributes['started_at'])),
            $this->getEndedAt($deploymentAttributes['ended_at']),
        );
    }

    /**
     * @param array<mixed> $siteResource
     * @param array<string, array<mixed>> $includedResources
     */
    private function getForgeSite(array $siteResource, array $includedResources): ForgeSite
    {
        $siteAttributes = Assert::isArray($siteResource['attributes']);
        $repositoryAttributes = Assert::isArray($siteAttributes['repository']);
        $relationships = Assert::isArray($siteResource['relationships']);
        $serverAttributes = $this->getIncludedAttributes(
            Assert::isArray($relationships['server'])['data'],
            $includedResources,
        );

        return new ForgeSite(
            Assert::string($siteResource['id']),
            Assert::string($siteAttributes['name']),
            Assert::string($siteAttributes['url']),
            SiteStatus::from(Assert::string($siteAttributes['status'])),
            Assert::nullOrString($siteAttributes['php_version']),
            Assert::nullOrString($repositoryAttributes['url']),
            Assert::nullOrString($repositoryAttributes['branch']),
            Assert::string($serverAttributes['name']),
            Assert::string($serverAttributes['slug']),
            Assert::nullOrString($serverAttributes['ip_address']),
            $this->getForgeDeployment(Assert::isArray($relationships['latestDeployment'])['data'], $includedResources),
        );
    }

    /**
     * @param list<mixed> $siteResources
     * @param array<string, array<mixed>> $includedResources
     */
    private function getForgeSites(array $siteResources, array $includedResources): ForgeSiteCollection
    {
        $forgeSites = new ForgeSiteCollection();

        foreach ($siteResources as $siteResource) {
            $forgeSites->push($this->getForgeSite(Assert::isArray($siteResource), $includedResources));
        }

        return $forgeSites;
    }

    /**
     * @param array<string, array<mixed>> $includedResources
     *
     * @return array<mixed>
     */
    private function getIncludedAttributes(mixed $resourceIdentifier, array $includedResources): array
    {
        $resourceKey = $this->getResourceKey(Assert::isArray($resourceIdentifier));
        Assert::keyExists($includedResources, $resourceKey);

        return Assert::isArray($includedResources[$resourceKey]['attributes']);
    }

    /**
     * @param array<mixed> $sitesResponse
     *
     * @return array<string, array<mixed>>
     */
    private function getIncludedResources(array $sitesResponse): array
    {
        if (!array_key_exists('included', $sitesResponse)) {
            return [];
        }

        $includedResources = [];

        foreach (Assert::isList($sitesResponse['included']) as $includedResource) {
            Assert::isArray($includedResource);
            $includedResources[$this->getResourceKey($includedResource)] = $includedResource;
        }

        return $includedResources;
    }

    /**
     * @param array<mixed> $metaResponse
     */
    private function getNextCursor(array $metaResponse): ?string
    {
        return Assert::nullOrString($metaResponse['next_cursor']);
    }

    /**
     * @param array<mixed> $resource
     */
    private function getResourceKey(array $resource): string
    {
        return sprintf('%s:%s', Assert::string($resource['type']), Assert::string($resource['id']));
    }
}
