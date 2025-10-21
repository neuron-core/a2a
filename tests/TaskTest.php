<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\Enum\TaskState;
use NeuronCore\A2A\Model\Message;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Model\Task;
use NeuronCore\A2A\Model\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function test_creates_basic_task(): void
    {
        $status = new TaskStatus(
            TaskState::QUEUED,
            new TextPart('Task created')
        );

        $task = new Task(
            id: 'task-123',
            contextId: 'context-456',
            status: $status
        );

        $this->assertSame('task-123', $task->id);
        $this->assertSame('context-456', $task->contextId);
        $this->assertSame($status, $task->status);
        $this->assertNull($task->history);
        $this->assertNull($task->artifacts);
        $this->assertNull($task->metadata);
    }

    public function test_creates_task_with_history(): void
    {
        $status = new TaskStatus(
            TaskState::RUNNING,
            new TextPart('Processing')
        );

        $message = new Message('user', [new TextPart('Hello')]);

        $task = new Task(
            id: 'task-123',
            contextId: 'context-456',
            status: $status,
            history: [$message]
        );

        $this->assertCount(1, $task->history);
        $this->assertSame($message, $task->history[0]);
    }

    public function test_converts_task_to_array(): void
    {
        $status = new TaskStatus(
            TaskState::COMPLETED,
            new TextPart('Done')
        );

        $task = new Task(
            id: 'task-123',
            contextId: 'context-456',
            status: $status
        );

        $array = $task->toArray();

        $this->assertSame('task', $array['kind']);
        $this->assertSame('task-123', $array['id']);
        $this->assertSame('context-456', $array['contextId']);
        $this->assertIsArray($array['status']);
        $this->assertSame('completed', $array['status']['state']);
    }

    public function test_converts_task_with_history_to_array(): void
    {
        $status = new TaskStatus(
            TaskState::COMPLETED,
            new TextPart('Done')
        );

        $message = new Message('user', [new TextPart('Hello')]);

        $task = new Task(
            id: 'task-123',
            contextId: 'context-456',
            status: $status,
            history: [$message]
        );

        $array = $task->toArray();

        $this->assertArrayHasKey('history', $array);
        $this->assertIsArray($array['history']);
        $this->assertCount(1, $array['history']);
        $this->assertSame('user', $array['history'][0]['role']);
    }

    public function test_converts_task_with_metadata_to_array(): void
    {
        $status = new TaskStatus(
            TaskState::QUEUED,
            new TextPart('Waiting')
        );

        $task = new Task(
            id: 'task-123',
            contextId: 'context-456',
            status: $status,
            metadata: ['priority' => 'high', 'tags' => ['urgent']]
        );

        $array = $task->toArray();

        $this->assertArrayHasKey('metadata', $array);
        $this->assertSame(['priority' => 'high', 'tags' => ['urgent']], $array['metadata']);
    }
}
