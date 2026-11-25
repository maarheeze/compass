<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Webmozart\Assert\Assert;

use function base64_decode;
use function sprintf;

readonly class BlobQuery
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organisation,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     * @throws RequestBudgetExhausted
     */
    public function text(string $repositoryName, string $oid): string
    {
        $response = $this->gitHubClient->get(sprintf('repos/%s/%s/git/blobs/%s', $this->organisation, $repositoryName, $oid));

        return Assert::string(base64_decode(Assert::string($response->json('content')), true));
    }
}
