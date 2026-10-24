<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Forge;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class DeploymentStateClassifier
{
    public function __construct(
        private ClockInterface $clock,
        private int $autoDeployGraceMinutes,
    ) {
    }

    public function classify(Site $site): DeploymentState
    {
        $repository = $site->repository;

        if ($repository === null) {
            return DeploymentState::UNKNOWN;
        }

        if ($site->repository_branch !== $repository->default_branch) {
            return DeploymentState::UNKNOWN;
        }

        if ($site->deployed_commit_hash === null) {
            return DeploymentState::UNKNOWN;
        }

        if ($site->deployed_commit_hash === $repository->last_commit_sha) {
            return DeploymentState::UP_TO_DATE;
        }

        if ($site->auto_deploy !== true) {
            return DeploymentState::BEHIND;
        }

        return $this->classifyAutoDeployment($repository);
    }

    private function classifyAutoDeployment(Repository $repository): DeploymentState
    {
        $graceEndsAt = $repository->last_commit_committed_at->addMinutes($this->autoDeployGraceMinutes);

        if (CarbonImmutable::instance($this->clock->now())->isBefore($graceEndsAt)) {
            return DeploymentState::DEPLOYING;
        }

        return DeploymentState::DEPLOYMENT_OVERDUE;
    }
}
