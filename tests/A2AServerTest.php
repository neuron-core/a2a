<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Enum\TaskState;
use NeuronCore\A2A\Example\InMemoryTaskRepository;
use NeuronCore\A2A\JsonRpc\JsonRpcError;
use NeuronCore\A2A\JsonRpc\JsonRpcRequest;
use NeuronCore\A2A\JsonRpc\JsonRpcResponse;
use NeuronCore\A2A\Model\AgentCard\AgentCard;
use NeuronCore\A2A\Model\AgentCard\AgentProvider;
use NeuronCore\A2A\Model\Message;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Model\Task;
use NeuronCore\A2A\Model\TaskStatus;
use NeuronCore\A2A\Server\A2AServer;
use PHPUnit\Framework\TestCase;

class A2AServerTest extends TestCase
{
    public function test_handles_message_send_for_new_task(): void
    {
        $server = $this->createServer();

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'message/send',
            params: [
                'messages' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['kind' => 'text', 'text' => 'Hello'],
                        ],
                    ],
                ],
            ],
            id: 1
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertIsArray($response->result);
        $this->assertSame('task', $response->result['kind']);
        $this->assertArrayHasKey('id', $response->result);
    }

    public function test_handles_tasks_get(): void
    {
        $server = $this->createServer();
        $task = $this->createTask('task-123', 'context-123');
        $server->getTestRepository()->save($task);

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'tasks/get',
            params: ['taskId' => 'task-123'],
            id: 2
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame('task-123', $response->result['id']);
    }

    public function test_handles_tasks_get_with_non_existent_task(): void
    {
        $server = $this->createServer();

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'tasks/get',
            params: ['taskId' => 'non-existent'],
            id: 3
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcError::class, $response);
        $this->assertSame(-32601, $response->code);
    }

    public function test_handles_tasks_list(): void
    {
        $server = $this->createServer();
        $server->getTestRepository()->save($this->createTask('task-1', 'context-1'));
        $server->getTestRepository()->save($this->createTask('task-2', 'context-1'));

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'tasks/list',
            params: ['contextId' => 'context-1'],
            id: 4
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertIsArray($response->result);
        $this->assertArrayHasKey('tasks', $response->result);
        $this->assertArrayHasKey('total', $response->result);
        $this->assertCount(2, $response->result['tasks']);
        $this->assertSame(2, $response->result['total']);
    }

    public function test_handles_tasks_cancel(): void
    {
        $server = $this->createServer();
        $task = $this->createTask('task-123', 'context-123');
        $server->getTestRepository()->save($task);

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'tasks/cancel',
            params: ['taskId' => 'task-123'],
            id: 5
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame('canceled', $response->result['status']['state']);
    }

    public function test_handles_tasks_cancel_for_terminal_task(): void
    {
        $server = $this->createServer();
        $task = new Task(
            id: 'task-123',
            contextId: 'context-123',
            status: new TaskStatus(TaskState::COMPLETED, new TextPart('Done'))
        );
        $server->getTestRepository()->save($task);

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'tasks/cancel',
            params: ['taskId' => 'task-123'],
            id: 6
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertSame('completed', $response->result['status']['state']);
    }

    public function test_handles_agent_get_authenticated_extended_card(): void
    {
        $server = $this->createServer();

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'agent/getAuthenticatedExtendedCard',
            params: null,
            id: 7
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcResponse::class, $response);
        $this->assertIsArray($response->result);
        $this->assertArrayHasKey('name', $response->result);
        $this->assertSame('Test Agent', $response->result['name']);
    }

    public function test_returns_error_for_unknown_method(): void
    {
        $server = $this->createServer();

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'unknown/method',
            params: null,
            id: 8
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcError::class, $response);
        $this->assertSame(-32601, $response->code);
    }

    public function test_prevents_modifying_terminal_task(): void
    {
        $server = $this->createServer();
        $task = new Task(
            id: 'task-123',
            contextId: 'context-123',
            status: new TaskStatus(TaskState::COMPLETED, new TextPart('Done')),
            history: []
        );
        $server->getTestRepository()->save($task);

        $request = new JsonRpcRequest(
            jsonrpc: '2.0',
            method: 'message/send',
            params: [
                'taskId' => 'task-123',
                'messages' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['kind' => 'text', 'text' => 'Try to modify'],
                        ],
                    ],
                ],
            ],
            id: 9
        );

        $response = $server->handleRequest($request);

        $this->assertInstanceOf(JsonRpcError::class, $response);
        $this->assertSame(-32601, $response->code);
    }

    protected function createServer(): TestA2AServer
    {
        return new TestA2AServer();
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

class TestA2AServer extends A2AServer
{
    protected InMemoryTaskRepository $repository;
    protected TestMessageHandler $handler;

    public function __construct()
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new TestMessageHandler();
    }

    protected function taskRepository(): TaskRepositoryInterface
    {
        return $this->repository;
    }

    protected function messageHandler(): MessageHandlerInterface
    {
        return $this->handler;
    }

    protected function agentCard(): AgentCard
    {
        return new AgentCard(
            protocolVersion: '1.0',
            name: 'Test Agent',
            description: 'Test agent for unit tests',
            url: 'http://localhost',
            preferredTransport: 'http',
            version: '1.0.0',
            provider: new AgentProvider(
                organization: 'Test Provider',
                url: 'http://localhost'
            )
        );
    }

    public function getTestRepository(): InMemoryTaskRepository
    {
        return $this->repository;
    }
}

class TestMessageHandler implements MessageHandlerInterface
{
    public function handle(Task $task, array $messages): Task
    {
        $history = $task->history ?? [];
        foreach ($messages as $message) {
            $history[] = $message;
        }

        $agentResponse = new Message('agent', [new TextPart('Response')]);
        $history[] = $agentResponse;

        return new Task(
            id: $task->id,
            contextId: $task->contextId,
            status: new TaskStatus(
                TaskState::COMPLETED,
                new TextPart('Task completed')
            ),
            history: $history,
            artifacts: $task->artifacts,
            metadata: $task->metadata
        );
    }
}
