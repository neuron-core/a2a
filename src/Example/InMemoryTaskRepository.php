<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Example;

use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Model\Task;

final class InMemoryTaskRepository implements TaskRepositoryInterface
{
    /**
     * @var array<string, Task>
     */
    protected array $tasks = [];

    public function save(Task $task): void
    {
        $this->tasks[$task->id] = $task;
    }

    public function find(string $taskId): ?Task
    {
        return $this->tasks[$taskId] ?? null;
    }

    public function findAll(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        $tasks = $this->tasks;

        if (isset($filters['contextId'])) {
            $tasks = \array_filter($tasks, fn (Task $task): bool => $task->contextId === $filters['contextId']);
        }

        $tasks = \array_values($tasks);

        if ($offset !== null) {
            $tasks = \array_slice($tasks, $offset);
        }

        if ($limit !== null) {
            return \array_slice($tasks, 0, $limit);
        }

        return $tasks;
    }

    public function count(array $filters = []): int
    {
        if ($filters === []) {
            return \count($this->tasks);
        }

        return \count($this->findAll($filters));
    }

    public function generateTaskId(): string
    {
        return \uniqid('task_', true);
    }

    public function generateContextId(): string
    {
        return \uniqid('context_', true);
    }
}
