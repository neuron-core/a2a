<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\Enum\TaskState;
use NeuronCore\A2A\Example\InMemoryTaskRepository;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Model\Task;
use NeuronCore\A2A\Model\TaskStatus;
use PHPUnit\Framework\TestCase;

class InMemoryTaskRepositoryTest extends TestCase
{
    public function test_saves_and_finds_task(): void
    {
        $repository = new InMemoryTaskRepository();
        $task = $this->createTask('task-1', 'context-1');

        $repository->save($task);

        $foundTask = $repository->find('task-1');
        $this->assertSame($task, $foundTask);
    }

    public function test_returns_null_for_non_existent_task(): void
    {
        $repository = new InMemoryTaskRepository();

        $result = $repository->find('non-existent');

        $this->assertNull($result);
    }

    public function test_finds_all_tasks(): void
    {
        $repository = new InMemoryTaskRepository();
        $task1 = $this->createTask('task-1', 'context-1');
        $task2 = $this->createTask('task-2', 'context-2');

        $repository->save($task1);
        $repository->save($task2);

        $tasks = $repository->findAll();

        $this->assertCount(2, $tasks);
    }

    public function test_filters_tasks_by_context_id(): void
    {
        $repository = new InMemoryTaskRepository();
        $task1 = $this->createTask('task-1', 'context-1');
        $task2 = $this->createTask('task-2', 'context-1');
        $task3 = $this->createTask('task-3', 'context-2');

        $repository->save($task1);
        $repository->save($task2);
        $repository->save($task3);

        $tasks = $repository->findAll(['contextId' => 'context-1']);

        $this->assertCount(2, $tasks);
        $this->assertSame('task-1', $tasks[0]->id);
        $this->assertSame('task-2', $tasks[1]->id);
    }

    public function test_applies_limit(): void
    {
        $repository = new InMemoryTaskRepository();
        $repository->save($this->createTask('task-1', 'context-1'));
        $repository->save($this->createTask('task-2', 'context-1'));
        $repository->save($this->createTask('task-3', 'context-1'));

        $tasks = $repository->findAll([], limit: 2);

        $this->assertCount(2, $tasks);
    }

    public function test_applies_offset(): void
    {
        $repository = new InMemoryTaskRepository();
        $repository->save($this->createTask('task-1', 'context-1'));
        $repository->save($this->createTask('task-2', 'context-1'));
        $repository->save($this->createTask('task-3', 'context-1'));

        $tasks = $repository->findAll([], offset: 1);

        $this->assertCount(2, $tasks);
        $this->assertSame('task-2', $tasks[0]->id);
    }

    public function test_applies_limit_and_offset(): void
    {
        $repository = new InMemoryTaskRepository();
        $repository->save($this->createTask('task-1', 'context-1'));
        $repository->save($this->createTask('task-2', 'context-1'));
        $repository->save($this->createTask('task-3', 'context-1'));

        $tasks = $repository->findAll([], limit: 1, offset: 1);

        $this->assertCount(1, $tasks);
        $this->assertSame('task-2', $tasks[0]->id);
    }

    public function test_counts_all_tasks(): void
    {
        $repository = new InMemoryTaskRepository();
        $repository->save($this->createTask('task-1', 'context-1'));
        $repository->save($this->createTask('task-2', 'context-2'));

        $count = $repository->count();

        $this->assertSame(2, $count);
    }

    public function test_counts_filtered_tasks(): void
    {
        $repository = new InMemoryTaskRepository();
        $repository->save($this->createTask('task-1', 'context-1'));
        $repository->save($this->createTask('task-2', 'context-1'));
        $repository->save($this->createTask('task-3', 'context-2'));

        $count = $repository->count(['contextId' => 'context-1']);

        $this->assertSame(2, $count);
    }

    public function test_generates_unique_task_id(): void
    {
        $repository = new InMemoryTaskRepository();

        $id1 = $repository->generateTaskId();
        $id2 = $repository->generateTaskId();

        $this->assertStringStartsWith('task_', $id1);
        $this->assertStringStartsWith('task_', $id2);
        $this->assertNotSame($id1, $id2);
    }

    public function test_generates_unique_context_id(): void
    {
        $repository = new InMemoryTaskRepository();

        $id1 = $repository->generateContextId();
        $id2 = $repository->generateContextId();

        $this->assertStringStartsWith('context_', $id1);
        $this->assertStringStartsWith('context_', $id2);
        $this->assertNotSame($id1, $id2);
    }

    protected function createTask(string $id, string $contextId): Task
    {
        return new Task(
            id: $id,
            contextId: $contextId,
            status: new TaskStatus(
                TaskState::QUEUED,
                new TextPart('Task created')
            )
        );
    }
}
