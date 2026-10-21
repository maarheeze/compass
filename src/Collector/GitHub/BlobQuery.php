<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Webmozart\Assert\Assert;

readonly class BlobQuery
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     */
    public function text(string $repositoryName, string $oid): string
    {
        $response = $this->gitHubClient->query($this->getQuery(), [
            'organization' => $this->organization,
            'name' => $repositoryName,
            'oid' => $oid,
        ]);

        return Assert::string($response->json('data.repository.object.text'));
    }

    private function getQuery(): string
    {
        return <<<'GRAPHQL'
            query ($organization: String!, $name: String!, $oid: GitObjectID!) {
              repository(owner: $organization, name: $name) {
                object(oid: $oid) {
                  ... on Blob { text }
                }
              }
            }
            GRAPHQL;
    }
}
