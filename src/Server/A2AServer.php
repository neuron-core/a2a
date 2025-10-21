<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Server;

use NeuronCore\A2A\Contract\AgentCardProviderInterface;
use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Enum\TaskState;
use NeuronCore\A2A\JsonRpc\JsonRpcError;
use NeuronCore\A2A\JsonRpc\JsonRpcRequest;
use NeuronCore\A2A\JsonRpc\JsonRpcResponse;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Model\Response\ListTasksResult;
use NeuronCore\A2A\Model\Task;
use NeuronCore\A2A\Model\TaskStatus;

final class A2AServer
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected MessageHandlerInterface $messageHandler,
        protected AgentCardProviderInterface $agentCardProvider,
    ) {
    }

    public function handleRequest(JsonRpcRequest $request): JsonRpcResponse|JsonRpcError
    {
        try {
            $result = match ($request->method) {
                'message/send' => $this->handleMessageSend($request->params),
                'tasks/get' => $this->handleTasksGet($request->params),
                'tasks/list' => $this->handleTasksList($request->params),
                'tasks/cancel' => $this->handleTasksCancel($request->params),
                'agent/getAuthenticatedExtendedCard' => $this->handleGetAgentCard(),
                default => throw new \InvalidArgumentException("Method not found: {$request->method}"),
            };

            return new JsonRpcResponse(result: $result, id: $request->id);
        } catch (\InvalidArgumentException $e) {
            return new JsonRpcError(
                code: -32601,
                message: $e->getMessage(),
                id: $request->id,
            );
        } catch (\Throwable $e) {
            return new JsonRpcError(
                code: -32603,
                message: 'Internal error',
                data: ['error' => $e->getMessage()],
                id: $request->id,
            );
        }
    }

    protected function handleMessageSend(mixed $params): array
    {
        $params = RequestParser::parseMessageSendParams($params);

        $task = $params->taskId !== null
            ? $this->taskRepository->find($params->taskId)
            : null;

        if (!$task instanceof Task) {
            $task = new Task(
                id: $this->taskRepository->generateTaskId(),
                contextId: $this->taskRepository->generateContextId(),
                status: new TaskStatus(
                    state: TaskState::QUEUED,
                    message: new TextPart('Task created'),
                ),
                history: [],
            );
        }

        if ($task->status->state->isTerminal()) {
            throw new \InvalidArgumentException('Task is in terminal state and cannot be modified');
        }

        $task = $this->messageHandler->handle($task, $params->messages);
        $this->taskRepository->save($task);

        return $task->toArray();
    }

    protected function handleTasksGet(mixed $params): array
    {
        $params = (array) $params;
        $taskId = $params['taskId'] ?? throw new \InvalidArgumentException('taskId is required');

        $task = $this->taskRepository->find($taskId);

        if (!$task instanceof Task) {
            throw new \InvalidArgumentException('Task not found');
        }

        return $task->toArray();
    }

    protected function handleTasksList(mixed $params): array
    {
        $params = RequestParser::parseListTasksParams($params);

        $filters = [];
        if ($params->contextId !== null) {
            $filters['contextId'] = $params->contextId;
        }

        $tasks = $this->taskRepository->findAll($filters, $params->limit, $params->offset);
        $total = $this->taskRepository->count($filters);

        $result = new ListTasksResult($tasks, $total);

        return $result->toArray();
    }

    protected function handleTasksCancel(mixed $params): array
    {
        $params = (array) $params;
        $taskId = $params['taskId'] ?? throw new \InvalidArgumentException('taskId is required');

        $task = $this->taskRepository->find($taskId);

        if (!$task instanceof Task) {
            throw new \InvalidArgumentException('Task not found');
        }

        if ($task->status->state->isTerminal()) {
            return $task->toArray();
        }

        $task = new Task(
            id: $task->id,
            contextId: $task->contextId,
            status: new TaskStatus(
                state: TaskState::CANCELED,
                message: new TextPart('Task canceled by user'),
            ),
            history: $task->history,
            artifacts: $task->artifacts,
            metadata: $task->metadata,
        );

        $this->taskRepository->save($task);

        return $task->toArray();
    }

    protected function handleGetAgentCard(): array
    {
        return $this->agentCardProvider->getAgentCard()->toArray();
    }
}
