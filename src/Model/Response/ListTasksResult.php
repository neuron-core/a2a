<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\Response;

use NeuronCore\A2A\Model\Task;

class ListTasksResult
{
    /**
     * @param array<Task> $tasks
     */
    public function __construct(
        public array $tasks,
        public int $total,
    ) {
    }

    public function toArray(): array
    {
        return [
            'tasks' => \array_map(fn (Task $task): array => $task->toArray(), $this->tasks),
            'total' => $this->total,
        ];
    }
}
