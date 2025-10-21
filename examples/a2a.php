<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use NeuronCore\A2A\Contract\AgentCardProviderInterface;
use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Enum\TaskState;
use NeuronCore\A2A\Example\InMemoryTaskRepository;
use NeuronCore\A2A\Http\A2AHttpHandler;
use NeuronCore\A2A\Http\HttpRequestInterface;
use NeuronCore\A2A\Http\HttpResponseInterface;
use NeuronCore\A2A\Model\AgentCard\AgentCard;
use NeuronCore\A2A\Model\AgentCard\AgentProvider;
use NeuronCore\A2A\Model\AgentCard\AgentSkill;
use NeuronCore\A2A\Model\Artifact;
use NeuronCore\A2A\Model\Message;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Model\Task;
use NeuronCore\A2A\Model\TaskStatus;
use NeuronCore\A2A\Server\A2AServer;

// 1. Create a simple message handler implementation
class SimpleMessageHandler implements MessageHandlerInterface
{
    public function handle(Task $task, array $messages): Task
    {
        // Merge new messages with existing history
        $history = $task->history ?? [];
        $history = array_merge($history, $messages);

        // Process the last user message
        $lastMessage = end($messages);
        $userText = '';

        foreach ($lastMessage->parts as $part) {
            if ($part instanceof TextPart) {
                $userText .= $part->text;
            }
        }

        // Create a simple response
        $agentMessage = new Message(
            role: 'agent',
            parts: [
                new TextPart("Hello! You said: {$userText}"),
            ],
        );

        $history[] = $agentMessage;

        // Create an artifact with the response
        $artifact = new Artifact(
            id: uniqid('artifact_', true),
            parts: [
                new TextPart("Response to: {$userText}"),
            ],
        );

        // Return updated task with completed status
        return new Task(
            id: $task->id,
            contextId: $task->contextId,
            status: new TaskStatus(
                state: TaskState::COMPLETED,
                message: new TextPart('Task completed successfully'),
            ),
            history: $history,
            artifacts: [$artifact],
            metadata: $task->metadata,
        );
    }
}

// 2. Create an agent card provider
class SimpleAgentCardProvider implements AgentCardProviderInterface
{
    public function getAgentCard(): AgentCard
    {
        return new AgentCard(
            protocolVersion: '0.3.0',
            name: 'Example A2A Agent',
            description: 'A simple example agent that echoes messages',
            url: 'https://example.com/a2a',
            preferredTransport: 'JSONRPC',
            version: '1.0.0',
            provider: new AgentProvider(
                organization: 'Example Organization',
                url: 'https://example.com',
            ),
            skills: [
                new AgentSkill(
                    id: 'echo',
                    name: 'Echo Skill',
                    description: 'Echoes back user messages',
                    tags: ['communication', 'example'],
                    examples: ['Say hello', 'Echo my message'],
                    inputModes: ['text/plain'],
                    outputModes: ['text/plain'],
                ),
            ],
            streaming: false,
            pushNotifications: false,
            stateTransitionHistory: false,
        );
    }
}

// 3. Create simple HTTP request/response adapters
class SimpleHttpRequest implements HttpRequestInterface
{
    public function __construct(
        protected string $method,
        protected string $path,
        protected string $body,
        protected array $headers = [],
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }
}

class SimpleHttpResponse implements HttpResponseInterface
{
    protected int $statusCode = 200;
    protected array $headers = [];
    protected string $body = '';

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function send(): void
    {
        echo "HTTP/1.1 {$this->statusCode}\n";
        foreach ($this->headers as $name => $value) {
            echo "{$name}: {$value}\n";
        }
        echo "\n";
        echo $this->body;
    }
}

// 4. Initialize the A2A server
$taskRepository = new InMemoryTaskRepository();
$messageHandler = new SimpleMessageHandler();
$agentCardProvider = new SimpleAgentCardProvider();

$server = new A2AServer(
    taskRepository: $taskRepository,
    messageHandler: $messageHandler,
    agentCardProvider: $agentCardProvider,
);

$httpHandler = new A2AHttpHandler(
    server: $server,
    agentCardProvider: $agentCardProvider,
);

// 5. Example 1: Get Agent Card
echo "=== Example 1: Get Agent Card ===\n\n";

$request = new SimpleHttpRequest(
    method: 'GET',
    path: '/.well-known/agent-card.json',
    body: '',
);

$response = new SimpleHttpResponse();
$httpHandler->handle($request, $response);

echo "\n\n";

// 6. Example 2: Send a message
echo "=== Example 2: Send a Message ===\n\n";

$jsonRpcRequest = json_encode([
    'jsonrpc' => '2.0',
    'id' => 1,
    'method' => 'message/send',
    'params' => [
        'messages' => [
            [
                'role' => 'user',
                'parts' => [
                    [
                        'kind' => 'text',
                        'text' => 'Hello, AI Agent!',
                    ],
                ],
            ],
        ],
    ],
]);

$request = new SimpleHttpRequest(
    method: 'POST',
    path: '/a2a',
    body: $jsonRpcRequest,
    headers: ['content-type' => 'application/json'],
);

$response = new SimpleHttpResponse();
$httpHandler->handle($request, $response);

echo "\n\n";

// 7. Example 3: List all tasks
echo "=== Example 3: List Tasks ===\n\n";

$jsonRpcRequest = json_encode([
    'jsonrpc' => '2.0',
    'id' => 2,
    'method' => 'tasks/list',
    'params' => [
        'limit' => 10,
    ],
]);

$request = new SimpleHttpRequest(
    method: 'POST',
    path: '/a2a',
    body: $jsonRpcRequest,
);

$response = new SimpleHttpResponse();
$httpHandler->handle($request, $response);

echo "\n";
