<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteProcesses;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum QueueWorkerState: string implements LabelledEnum
{
    case FAILING = 'failing';
    case NOT_CONFIGURED = 'not_configured';
    case RUNNING = 'running';
    case STARTING = 'starting';
    case STOPPED = 'stopped';
    case UNKNOWN = 'unknown';

    /**
     * @return list<self>
     */
    public static function worstFirst(): array
    {
        return [
            self::FAILING,
            self::STOPPED,
            self::UNKNOWN,
            self::STARTING,
            self::RUNNING,
        ];
    }

    public function labelKey(): string
    {
        return sprintf('compass::queue-worker-state.%s', $this->name);
    }
}
