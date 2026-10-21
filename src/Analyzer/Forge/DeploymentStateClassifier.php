<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Forge;

use Maarheeze\Compass\Models\Site;

readonly class DeploymentStateClassifier
{
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

        if ($site->deployed_commit_hash !== $repository->last_commit_sha) {
            return DeploymentState::BEHIND;
        }

        return DeploymentState::UP_TO_DATE;
    }
}
