<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Enum;

enum TaskState: string
{
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case INPUT_REQUIRED = 'input-required';
    case AUTH_REQUIRED = 'auth-required';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case REJECTED = 'rejected';
    case FAILED = 'failed';

    public function isTerminal(): bool
    {
        return \in_array($this, [
            self::COMPLETED,
            self::CANCELED,
            self::REJECTED,
            self::FAILED,
        ], true);
    }
}
