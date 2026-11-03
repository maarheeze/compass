<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Carbon\CarbonImmutable;
use Generator;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\Sites\CollectedSite;
use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
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
     * @return Generator<int, SitesQueryResult>
     *
     * @throws ForgeRequestFailed
     */
    public function pages(): Generator
    {
        $sitesQueryResult = $this->getSitesQueryResult(null);

        yield $sitesQueryResult;

        while ($sitesQueryResult->nextCursor !== null) {
            $sitesQueryResult = $this->getSitesQueryResult($sitesQueryResult->nextCursor);

            yield $sitesQueryResult;
        }
    }

    /**
     * @param array<mixed> $siteResource
     * @param array<string, array<mixed>> $includedResources
     */
    private function getCollectedSite(array $siteResource, array $includedResources): CollectedSite
    {
        $siteAttributes = Assert::isArray($siteResource['attributes']);
        $repositoryAttributes = Assert::isArray($siteAttributes['repository']);
        $relationships = Assert::isArray($siteResource['relationships']);
        $serverAttributes = $this->getIncludedAttributes(
            Assert::isArray($relationships['server'])['data'],
            $includedResources,
        );
        $deploymentAttributes = $this->getDeploymentAttributes(
            Assert::isArray($relationships['latestDeployment'])['data'],
            $includedResources,
        );

        return new CollectedSite(
            Assert::string($siteResource['id']),
            Assert::string($siteAttributes['name']),
            Assert::string($siteAttributes['url']),
            Assert::nullOrString($siteAttributes['php_version']),
            Assert::nullOrString($repositoryAttributes['url']),
            Assert::nullOrString($repositoryAttributes['branch']),
            Assert::nullOrBoolean($siteAttributes['quick_deploy']),
            Assert::string($serverAttributes['name']),
            Assert::nullOrString($serverAttributes['ip_address']),
            $this->getDeploymentStatus($deploymentAttributes),
            $this->getDeployedCommitHash($deploymentAttributes),
            $this->getDeploymentStartedAt($deploymentAttributes),
            $this->getDeploymentEndedAt($deploymentAttributes),
        );
    }

    /**
     * @param list<mixed> $siteResources
     * @param array<string, array<mixed>> $includedResources
     */
    private function getCollectedSites(array $siteResources, array $includedResources): CollectedSiteCollection
    {
        $collectedSites = new CollectedSiteCollection();

        foreach ($siteResources as $siteResource) {
            $collectedSites->push($this->getCollectedSite(Assert::isArray($siteResource), $includedResources));
        }

        return $collectedSites;
    }

    /**
     * @param ?array<mixed> $deploymentAttributes
     */
    private function getDeployedCommitHash(?array $deploymentAttributes): ?string
    {
        if ($deploymentAttributes === null) {
            return null;
        }

        return Assert::nullOrString(Assert::isArray($deploymentAttributes['commit'])['hash']);
    }

    /**
     * @param array<string, array<mixed>> $includedResources
     *
     * @return ?array<mixed>
     */
    private function getDeploymentAttributes(mixed $deploymentIdentifier, array $includedResources): ?array
    {
        if ($deploymentIdentifier === null) {
            return null;
        }

        return $this->getIncludedAttributes($deploymentIdentifier, $includedResources);
    }

    /**
     * @param ?array<mixed> $deploymentAttributes
     */
    private function getDeploymentEndedAt(?array $deploymentAttributes): ?CarbonImmutable
    {
        if ($deploymentAttributes === null || $deploymentAttributes['ended_at'] === null) {
            return null;
        }

        return CarbonImmutable::parse(Assert::string($deploymentAttributes['ended_at']));
    }

    /**
     * @param ?array<mixed> $deploymentAttributes
     */
    private function getDeploymentStartedAt(?array $deploymentAttributes): ?CarbonImmutable
    {
        if ($deploymentAttributes === null) {
            return null;
        }

        return CarbonImmutable::parse(Assert::string($deploymentAttributes['started_at']));
    }

    /**
     * @param ?array<mixed> $deploymentAttributes
     */
    private function getDeploymentStatus(?array $deploymentAttributes): ?DeploymentStatus
    {
        if ($deploymentAttributes === null) {
            return null;
        }

        return match (Assert::string($deploymentAttributes['status'])) {
            'pending', 'queued' => DeploymentStatus::QUEUED,
            'deploying' => DeploymentStatus::RUNNING,
            'finished' => DeploymentStatus::SUCCEEDED,
            'failed', 'failed-build' => DeploymentStatus::FAILED,
            'cancelled' => DeploymentStatus::CANCELLED,
        };
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

    /**
     * @throws ForgeRequestFailed
     */
    private function getSitesQueryResult(?string $cursor): SitesQueryResult
    {
        $response = $this->forgeClient->get(sprintf('orgs/%s/sites', $this->organization), [
            'include' => 'server,latestDeployment',
            'page[size]' => 100,
            'page[cursor]' => $cursor,
        ]);
        $sitesResponse = Assert::isArray($response->json());
        $includedResources = $this->getIncludedResources($sitesResponse);

        return new SitesQueryResult(
            $this->getCollectedSites(Assert::isList($sitesResponse['data']), $includedResources),
            $this->getNextCursor(Assert::isArray($sitesResponse['meta'])),
        );
    }
}
