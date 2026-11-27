<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\Forge;

use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SiteProcesses\CollectedSiteProcesses;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Models\Site;
use UnexpectedValueException;
use Webmozart\Assert\Assert;

use function in_array;
use function sprintf;
use function str_contains;

readonly class SiteProcessesQuery
{
    private const string QUEUE_WORKER_COMMAND = 'queue:work';

    public function __construct(
        private ForgeClient $forgeClient,
        private string $organisation,
    ) {
    }

    /**
     * @throws ForgeRequestFailed
     * @throws RequestBudgetExhausted
     */
    public function siteProcesses(Site $site): CollectedSiteProcesses
    {
        $queueWorkerStates = [];
        $cursor = null;

        do {
            $backgroundProcessesResponse = $this->getBackgroundProcessesResponse($site, $cursor);

            foreach (Assert::isList($backgroundProcessesResponse['data']) as $backgroundProcessResource) {
                $queueWorkerState = $this->getQueueWorkerState(Assert::isArray($backgroundProcessResource));

                if ($queueWorkerState === null) {
                    continue;
                }

                $queueWorkerStates[] = $queueWorkerState;
            }

            $cursor = Assert::nullOrString(Assert::isArray($backgroundProcessesResponse['meta'])['next_cursor']);
        } while ($cursor !== null);

        return new CollectedSiteProcesses($this->getWorstQueueWorkerState($queueWorkerStates));
    }

    /**
     * @return array<mixed>
     *
     * @throws ForgeRequestFailed
     * @throws RequestBudgetExhausted
     */
    private function getBackgroundProcessesResponse(Site $site, ?string $cursor): array
    {
        $path = sprintf('orgs/%s/servers/%s/background-processes', $this->organisation, $site->server_external_id);
        $response = $this->forgeClient->get($path, [
            'filter[site_id]' => $site->external_id,
            'page[size]' => 100,
            'page[cursor]' => $cursor,
        ]);

        return Assert::isArray($response->json());
    }

    /**
     * @param array<mixed> $backgroundProcessResource
     */
    private function getQueueWorkerState(array $backgroundProcessResource): ?QueueWorkerState
    {
        $backgroundProcessAttributes = Assert::isArray($backgroundProcessResource['attributes']);

        if (!str_contains(Assert::string($backgroundProcessAttributes['command']), self::QUEUE_WORKER_COMMAND)) {
            return null;
        }

        return match (Assert::string($backgroundProcessAttributes['status'])) {
            'running' => QueueWorkerState::RUNNING,
            'installing', 'starting', 'restarting' => QueueWorkerState::STARTING,
            'stopping', 'stopped', 'removing' => QueueWorkerState::STOPPED,
            'fatal', 'backoff', 'exited' => QueueWorkerState::FAILING,
            'unknown' => QueueWorkerState::UNKNOWN,
            default => throw new UnexpectedValueException('unknown background process status'),
        };
    }

    /**
     * @param list<QueueWorkerState> $queueWorkerStates
     */
    private function getWorstQueueWorkerState(array $queueWorkerStates): QueueWorkerState
    {
        foreach (QueueWorkerState::worstFirst() as $queueWorkerState) {
            if (in_array($queueWorkerState, $queueWorkerStates, true)) {
                return $queueWorkerState;
            }
        }

        return QueueWorkerState::NOT_CONFIGURED;
    }
}
