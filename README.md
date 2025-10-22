# A2A PHP Server

A framework-agnostic PHP implementation of the **A2A (Agent-to-Agent) Protocol** that enables AI agents to communicate and collaborate across different platforms, frameworks, and organizations.

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Architecture](#architecture)
- [Quick Start](#quick-start)
  - [Standalone Usage](#standalone-usage)
  - [Laravel Integration](#laravel-integration)
- [Core Concepts](#core-concepts)
- [Complete Examples](#complete-examples)
- [API Reference](#api-reference)
- [Advanced Usage](#advanced-usage)

---

## Introduction

The **A2A (Agent-to-Agent) Protocol** is an open standard that enables seamless communication and collaboration between AI agents. It provides a common language for agents built using diverse frameworks and by different vendors, fostering interoperability and breaking down silos.

**Official Specification:** https://a2a-protocol.org/latest/specification/

### What This Library Provides

- ✅ **Complete A2A Protocol Implementation** - JSON-RPC 2.0 over HTTP
- ✅ **Multi-Agent Architecture**-Host multiple specialized agents in one application

---

## Features

### Supported A2A Methods

- ✅ `message/send` - Send messages and receive agent responses
- ✅ `tasks/get` - Retrieve a specific task by ID
- ✅ `tasks/list` - List tasks with filtering and pagination
- ✅ `tasks/cancel` - Cancel a running task
- ✅ `agent/getAuthenticatedExtendedCard` - Get agent capabilities

### Framework Support

- ✅ **Laravel—**Full integration with Artisan commands and routes
- ✅ **Standalone** - Framework-agnostic HTTP interfaces
- 🔄 **Other Frameworks—**Easy to add adapters (Symfony, Slim, etc.)

---

## Quick Start

### Standalone Usage

#### 1. Create Your Agent Server

You can create your own server class extending `NeuronCore\A2A\A2AServer`. This class provides the main entry point to 
expose your AI agent to the world. You need to implement two components to create a server:

- Task Repository - Store and retrieve tasks
- Message Handler - Handle messages and return task results

```php
use NeuronCore\A2A\Server\A2AServer;
use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Model\AgentCard\AgentCard;

class MyAIAgent extends A2AServer
{
    protected function taskRepository(): TaskRepositoryInterface
    {
        return new MyTaskRepository();
    }

    protected function messageHandler(): MessageHandlerInterface
    {
        return new MyMessageHandler();
    }

    protected function agentCard(): AgentCard
    {
        return new AgentCard(
            protocolVersion: '0.3.0',
            name: 'My AI Agent',
            description: 'A specialized AI agent for data analysis',
            url: 'https://example.com/a2a',
            preferredTransport: 'JSONRPC',
            version: '1.0.0',
            provider: new AgentProvider(
                organization: 'My Company',
                url: 'https://mycompany.com'
            ),
            skills: [
                new AgentSkill(
                    id: 'analysis',
                    name: 'Data Analysis',
                    description: 'Analyze datasets',
                    tags: ['data', 'analytics'],
                    examples: ['Analyze sales data'],
                    inputModes: ['text/plain'],
                    outputModes: ['text/plain']
                )
            ]
        );
    }
}
```

#### 2. Implement Task Repository

```php
use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Model\Task;

class MyTaskRepository implements TaskRepositoryInterface
{
    public function save(Task $task): void
    {
        // Save to database, Redis, etc.
    }

    public function find(string $taskId): ?Task
    {
        // Retrieve from storage
    }

    public function findAll(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        // Query with filters
    }

    public function count(array $filters = []): int
    {
        // Count tasks
    }

    public function generateTaskId(): string
    {
        return uniqid('task_', true);
    }

    public function generateContextId(): string
    {
        return uniqid('context_', true);
    }
}
```

#### 3. Implement Message Handler (Your AI Logic)

The message handler is responsible for handling incoming messages and returning task results. It's the place where you 
execute your AI Agent and return the results.

```php
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Model\Task;
use NeuronCore\A2A\Model\Message;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Model\Artifact;
use NeuronCore\A2A\Model\TaskStatus;
use NeuronCore\A2A\Enum\TaskState;

class MyMessageHandler implements MessageHandlerInterface
{
    public function handle(Task $task, array $messages): Task
    {
        $history = array_merge($task->history ?? [], $messages);

        // Extract user message
        $userText = $this->extractText($messages[0]);

        $aiResponse = Agent::make()
            ->setProvider(...)
            ->chat(new UserMessage($userText))
            ->getContent();

        // Create an agent response
        $agentMessage = new Message(
            role: 'agent',
            parts: [new TextPart($aiResponse)]
        );

        $history[] = $agentMessage;

        // Return a completed task
        return new Task(
            id: $task->id,
            contextId: $task->contextId,
            status: new TaskStatus(
                state: TaskState::COMPLETED,
                message: new TextPart('Task completed')
            ),
            history: $history,
        );
    }

    protected function extractText(Message $message): string
    {
        $text = '';
        foreach ($message->parts as $part) {
            if ($part instanceof TextPart) {
                $text .= $part->text;
            }
        }
        return $text;
    }
}
```

#### 4. Use the Server

```php
use NeuronCore\A2A\JsonRpc\JsonRpcRequest;

// Create a server instance
$server = new MyAIAgent();

// Handle JSON-RPC request
$request = JsonRpcRequest::fromArray([
    'jsonrpc' => '2.0',
    'id' => 1,
    'method' => 'message/send',
    'params' => [
        'messages' => [
            [
                'role' => 'user',
                'parts' => [
                    ['kind' => 'text', 'text' => 'Hello!']
                ]
            ]
        ]
    ]
]);

$response = $server->handleRequest($request);
echo json_encode($response->toArray());
```

**See `examples/a2a.php` for a complete working example.**

---

### Laravel Integration

Laravel gets first-class support with Artisan commands, service providers, and routing helpers.

#### 1. Register Service Provider

In `config/app.php` or `bootstrap/providers.php` based on your project structure:

```php
'providers' => [
    // ...
    NeuronCore\A2A\Laravel\A2AServiceProvider::class,
],
```

#### 2. Generate an Agent server

```bash
php artisan make:a2a DataAnalyst
```

**This generates:**
- `app/A2A/DataAnalystServer.php` - Main server
- `app/A2A/DataAnalystTaskRepository.php` - Task storage
- `app/A2A/DataAnalystMessageHandler.php` - AI logic

You must implement the Task Repository and Message Handler.

#### 4. Register Routes

In `routes/api.php`:

```php
use NeuronCore\A2A\Laravel\A2A;
use App\A2A\DataAnalystServer;

A2A::route('/a2a/data-analyst', DataAnalystServer::class)
    ->middleware(['auth:api']);
```

**Done!** Your agent is live at:
- `POST /a2a/data-analyst` - JSON-RPC endpoint
- `GET /a2a/data-analyst/.well-known/agent-card.json` - Agent card

#### Multiple Agents

Create and register as many agents as needed:

```bash
php artisan make:a2a DataAnalyst
php artisan make:a2a Translator
php artisan make:a2a CodeGenerator
```

```php
// routes/api.php
A2A::route('/a2a/data-analyst', DataAnalystServer::class);
A2A::route('/a2a/translator', TranslatorServer::class);
A2A::route('/a2a/code-generator', CodeGeneratorServer::class);
```

Each agent is completely independent with its own:
- Task repository
- Message handler (AI logic)
- Middleware configuration

---

## Core Concepts

### Message Structure

Messages follow the A2A protocol:

```php
new Message(
    role: 'user',  // or 'agent'
    parts: [
        new TextPart('Hello'),
        new FilePart($file, 'image/png'),
        new DataPart(['key' => 'value'], 'application/json')
    ]
)
```

### Agent Card

The agent card is a JSON manifest that describes:
- Agent identity (name, description, version)
- Provider information
- Available skills with examples
- Input/output formats
- Authentication requirements
- Protocol capabilities

### Task Context

Tasks can be grouped by `contextId` for conversation continuity:
- Multiple tasks can share the same context
- Use `tasks/list` with `contextId` filter to retrieve related tasks
- Useful for multi-turn conversations

---

## API Reference

### A2AServer Abstract Class

**Abstract Methods:**
```php
abstract protected function taskRepository(): TaskRepositoryInterface;
abstract protected function messageHandler(): MessageHandlerInterface;
abstract protected function agentCard(): AgentCard;
```

**Public Methods:**
```php
public function handleRequest(JsonRpcRequest $request): JsonRpcResponse|JsonRpcError;
public function getAgentCard(): array;
```

### TaskRepositoryInterface

```php
interface TaskRepositoryInterface
{
    public function save(Task $task): void;
    public function find(string $taskId): ?Task;
    public function findAll(array $filters = [], ?int $limit = null, ?int $offset = null): array;
    public function count(array $filters = []): int;
    public function generateTaskId(): string;
    public function generateContextId(): string;
}
```

### MessageHandlerInterface

```php
interface MessageHandlerInterface
{
    public function handle(Task $task, array $messages): Task;
}
```

---

## Advanced Usage

### Custom Task Repository

Use Eloquent, Redis, File, or any storage backend:

```php
class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function save(Task $task): void
    {
        TaskModel::updateOrCreate(
            ['id' => $task->id],
            ['data' => serialize($task)]
        );
    }

    public function find(string $taskId): ?Task
    {
        $model = TaskModel::find($taskId);
        return $model ? unserialize($model->data) : null;
    }
}
```

### Authentication & Middleware

Laravel example with custom middleware:

```php
A2A::route('/a2a/premium-agent', DataAnalystAgent::class)
    ->middleware(['auth:api']);
```

### File Attachments

Handle file uploads in messages:

```php
use NeuronCore\A2A\Model\Part\FilePart;
use NeuronCore\A2A\Model\File\FileWithBytes;
use NeuronCore\A2A\Model\File\FileWithUri;

// Embedded file
$filePart = new FilePart(
    file: new FileWithBytes(
        bytes: base64_encode($fileContents),
        fileName: 'document.pdf',
        mimeType: 'application/pdf'
    ),
    mimeType: 'application/pdf'
);

// File by URL
$filePart = new FilePart(
    file: new FileWithUri(
        uri: 'https://example.com/file.pdf',
        fileName: 'document.pdf',
        mimeType: 'application/pdf'
    ),
    mimeType: 'application/pdf'
);
```

### Structured Data

Send and receive structured JSON data:

```php
use NeuronCore\A2A\Model\Part\DataPart;

$dataPart = new DataPart(
    data: [
        'results' => [
            ['name' => 'Alice', 'score' => 95],
            ['name' => 'Bob', 'score' => 87]
        ],
        'total' => 2
    ],
    mimeType: 'application/json'
);
```

---

## Contributing

When contributing to this project:
- Use modern PHP 8.1+ features
- Maintain interface-driven design
- Write minimal, clean code
- Focus on simplicity and clarity

---

## License

MIT License—See LICENSE file for details
