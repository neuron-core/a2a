<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model;

use NeuronCore\A2A\Enum\TaskState;
use NeuronCore\A2A\Model\Part\PartInterface;

class TaskStatus
{
    public function __construct(
        public TaskState $state,
        public PartInterface $message,
    ) {
    }

    public function toArray(): array
    {
        return [
            'state' => $this->state->value,
            'message' => $this->message->toArray(),
        ];
    }
}
