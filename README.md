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
- ✅ **Multi-Agent Architecture—**Host multiple specialized agents in one application
- ✅ **Modern PHP 8.1+** - Enums, constructor promotion, readonly classes, union types

---

## Features

### Supported A2A Methods

- ✅ `message/send` - Send messages and receive agent responses
- ✅ `tasks/get` - Retrieve a specific task by ID
- ✅ `tasks/list` - List tasks with filtering and pagination
- ✅ `tasks/cancel` - Cancel a running task
- ✅ `agent/getAuthenticatedExtendedCard` - Get agent capabilities

### Agent Card

- ✅ Served at `/.well-known/agent-card.json`
- ✅ Describes agent capabilities, skills, and authentication
- ✅ Enables agent discovery and interoperability

### Framework Support

- ✅ **Laravel—**Full integration with Artisan commands and routing
- ✅ **Standalone** - Framework-agnostic HTTP interfaces
- 🔄 **Other Frameworks—**Easy to add adapters (Symfony, Slim, etc.)

---

## Architecture

### Project Structure

```
src/
├── Contract/                      # Core interfaces
│   ├── TaskRepositoryInterface.php
│   ├── MessageHandlerInterface.php
│   └── AgentCardProviderInterface.php (deprecated - use agentCard() method)
├── Enum/
│   └── TaskState.php              # Task lifecycle states
├── Model/                         # Domain models
│   ├── Task.php
│   ├── TaskStatus.php
│   ├── Message.php
│   ├── Artifact.php
│   ├── Part/                      # Message content types
│   │   ├── TextPart.php
│   │   ├── FilePart.php
│   │   └── DataPart.php
│   ├── File/                      # File attachment types
│   │   ├── FileWithBytes.php
│   │   └── FileWithUri.php
│   └── AgentCard/                 # Agent capability models
│       ├── AgentCard.php
│       ├── AgentProvider.php
│       └── AgentSkill.php
├── Server/
│   ├── A2AServer.php              # Abstract server (extend for each agent)
│   └── RequestParser.php          # JSON-RPC parsing
├── JsonRpc/                       # JSON-RPC protocol
│   ├── JsonRpcRequest.php
│   ├── JsonRpcResponse.php
│   └── JsonRpcError.php
├── Http/                          # Framework-agnostic HTTP
│   ├── HttpRequestInterface.php
│   ├── HttpResponseInterface.php
│   ├── A2AHttpHandler.php
│   └── Laravel/                   # Laravel integration
│       ├── A2AServiceProvider.php
│       ├── A2A.php                # Route helper
│       ├── A2AController.php
│       ├── LaravelHttpRequest.php
│       ├── LaravelHttpResponse.php
│       ├── Console/
│       │   └── MakeA2AServerCommand.php
│       └── Examples/
│           ├── DataAnalystServer.php
│           └── TranslatorServer.php
└── Example/
    └── InMemoryTaskRepository.php # Reference implementation
```

### Design Philosophy

**One Server = One Agent**

Each concrete `A2AServer` class represents a single specialized AI agent. This enables:
- Clear separation of concerns
- Independent configuration per agent
- Easy scaling and deployment
- Multiple agents in one application

**Interface-Driven**

Two core interfaces must be implemented for each agent:
1. `TaskRepositoryInterface` - How tasks are persisted
2. `MessageHandlerInterface` - How messages are processed (your AI logic)

The agent card is returned directly via the `agentCard()` method.

---

## Quick Start

### Standalone Usage

#### 1. Create Your Agent Server

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

```php
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

        // Call your AI service (OpenAI, Claude, local model, etc.)
        $aiResponse = $this->callAI($userText);

        // Create an agent response
        $agentMessage = new Message(
            role: 'agent',
            parts: [new TextPart($aiResponse)]
        );

        $history[] = $agentMessage;

        // Create artifact
        $artifact = new Artifact(
            id: uniqid('artifact_'),
            parts: [new TextPart($aiResponse)]
        );

        // Return a completed task
        return new Task(
            id: $task->id,
            contextId: $task->contextId,
            status: new TaskStatus(
                state: TaskState::COMPLETED,
                message: new TextPart('Task completed')
            ),
            history: $history,
            artifacts: [$artifact]
        );
    }

    protected function callAI(string $input): string
    {
        // Your AI integration here
        return "AI response to: {$input}";
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

In `config/app.php`:

```php
'providers' => [
    // ...
    NeuronCore\A2A\Laravel\A2AServiceProvider::class,
],
```

Or use Laravel 11+ auto-discovery.

#### 2. Generate an Agent

```bash
php artisan make:a2a DataAnalyst
```

**This generates:**
- `app/A2A/DataAnalystServer.php` - Main server
- `app/A2A/DataAnalystTaskRepository.php` - Task storage
- `app/A2A/DataAnalystMessageHandler.php` - AI logic
- `app/A2A/DataAnalystAgentCard.php` - Agent capabilities

#### 3. Implement Your AI Logic

Open `app/A2A/DataAnalystMessageHandler.php`:

```php
public function handle(Task $task, array $messages): Task
{
    // Your AI implementation
    $response = app(\OpenAI\Client::class)->chat()->create([
        'model' => 'gpt-4',
        'messages' => $this->convertMessages($messages),
    ]);

    // Return a completed task
    // ... (scaffolded code included)
}
```

#### 4. Configure Agent Card

Open `app/A2A/DataAnalystAgentCard.php` and update:
- Agent name and description
- Skills and capabilities
- Input/output formats
- Tags and examples

#### 5. Register Routes

In `routes/api.php`:

```php
use NeuronCore\A2A\Laravel\A2A;
use App\A2A\DataAnalystServer;

A2A::route('/a2a/data-analyst', DataAnalystServer::class)
    ->middleware(['auth:api', 'throttle:60,1']);
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
- Agent card (capabilities)
- Middleware configuration

---

## Core Concepts

### Task Lifecycle

Tasks progress through these states:

```
QUEUED → RUNNING → COMPLETED
                ↓
              FAILED
                ↓
             CANCELED
                ↓
             REJECTED
```

Terminal states: `COMPLETED`, `FAILED`, `CANCELED`, `REJECTED`

Once a task reaches a terminal state, it cannot be modified.

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

## Complete Examples

### Example 1: Echo Agent (Standalone)

**File:** `examples/a2a.php`

A complete working example:
- Creating a concrete A2AServer
- Implementing message handling
- Defining agent card
- Handling JSON-RPC requests
- Getting the agent card

Run with:
```bash
php examples/a2a.php
```

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

### JSON-RPC Methods

**message/send**
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "message/send",
  "params": {
    "taskId": "optional-existing-task-id",
    "messages": [
      {
        "role": "user",
        "parts": [
          {"kind": "text", "text": "Hello"}
        ]
      }
    ]
  }
}
```

**tasks/get**
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tasks/get",
  "params": {
    "taskId": "task_123"
  }
}
```

**tasks/list**
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tasks/list",
  "params": {
    "contextId": "context_abc",
    "limit": 10,
    "offset": 0
  }
}
```

**tasks/cancel**
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "tasks/cancel",
  "params": {
    "taskId": "task_123"
  }
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
A2A::route('/a2a/premium-agent', PremiumAgent::class)
    ->middleware(['auth:api', 'subscription:premium', 'throttle:1000,1']);
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
