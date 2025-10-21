<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Contract;

use NeuronCore\A2A\Model\Task;

interface TaskRepositoryInterface
{
    public function save(Task $task): void;

    public function find(string $taskId): ?Task;

    /**
     * @param array<string, mixed> $filters
     * @return array<Task>
     */
    public function findAll(array $filters = [], ?int $limit = null, ?int $offset = null): array;

    public function count(array $filters = []): int;

    public function generateTaskId(): string;

    public function generateContextId(): string;
}
