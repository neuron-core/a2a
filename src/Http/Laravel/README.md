# Laravel A2A Integration

This package provides Laravel-specific adapters for the A2A protocol server.

## Overview

The Laravel adapter allows you to expose **multiple AI agents** in a single Laravel application. Each agent is a separate class that extends `A2AServer` and can be registered on its own route with independent middleware.

## Quick Start

### 1. Install & Register the Service Provider

In `config/app.php`:

```php
'providers' => [
    // ...
    NeuronCore\A2A\Http\Laravel\A2AServiceProvider::class,
],
```

Or if using Laravel 11+ with auto-discovery, it will be registered automatically.

### 2. Generate Your Agent

Use the Artisan command to scaffold a new agent:

```bash
php artisan make:a2a DataAnalyst
```

This generates four files in `app/A2A/`:
- `DataAnalystServer.php` - Main server class
- `DataAnalystTaskRepository.php` - Task persistence
- `DataAnalystMessageHandler.php` - AI agent logic (your implementation)
- `DataAnalystAgentCard.php` - Agent capabilities definition

### 3. Implement Your Agent Logic

Open `DataAnalystMessageHandler.php` and add your AI logic:

```php
public function handle(Task $task, array $messages): Task
{
    // Call your AI service (OpenAI, Claude, etc.)
    $response = app(\OpenAI\Client::class)->chat()->create([
        'model' => 'gpt-4',
        'messages' => $this->convertToAIFormat($messages),
    ]);

    // Return completed task with response
    // ... (see generated file for full implementation)
}
```

### 4. Register Routes

In `routes/api.php`:

```php
use NeuronCore\A2A\Http\Laravel\A2A;
use App\A2A\DataAnalystServer;

A2A::route('/a2a/data-analyst', DataAnalystServer::class);
```

Done! Your agent is now available at:
- `POST /a2a/data-analyst` - Send messages
- `GET /a2a/data-analyst/.well-known/agent-card.json` - Get capabilities

## Manual Setup (Alternative)

If you prefer to create servers manually instead of using the generator:

### Create Your Agent Server Class

Each agent extends `A2AServer` and implements three factory methods:

```php
namespace App\Agents;

use NeuronCore\A2A\Server\A2AServer;
use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Contract\AgentCardProviderInterface;

class DataAnalystAgent extends A2AServer
{
    protected function createTaskRepository(): TaskRepositoryInterface
    {
        // Return your task repository implementation
        return app(EloquentTaskRepository::class);
    }

    protected function createMessageHandler(): MessageHandlerInterface
    {
        // Return your message handler - this is your AI agent logic
        return new DataAnalysisHandler(
            openai: app(\OpenAI\Client::class),
            // ... other dependencies
        );
    }

    protected function createAgentCardProvider(): AgentCardProviderInterface
    {
        // Return agent card with your agent's capabilities
        return new DataAnalystCardProvider();
    }
}
```

```

Each `A2A::route()` call registers **two endpoints**:
- `POST /a2a/data-analyst` - JSON-RPC endpoint
- `GET /a2a/data-analyst/.well-known/agent-card.json` - Agent card

The method returns a Laravel `Route` instance, so you can chain middleware:

```php
A2A::route('/a2a/data-analyst', DataAnalystServer::class)
    ->middleware(['auth:api', 'throttle:60,1']);
```

## Complete Example

See `src/Http/Laravel/Examples/` for complete working examples:
- `DataAnalystServer.php` - Data analysis agent
- `TranslatorServer.php` - Translation agent

## Artisan Command Reference

### make:a2a Command

```bash
php artisan make:a2a {name}
```

**Arguments:**
- `name` - The name of your agent (e.g., `DataAnalyst`, `Translator`, `CodeGenerator`)

**What it generates:**
- Server class: `app/A2A/{Name}Server.php`
- Task repository: `app/A2A/{Name}TaskRepository.php`
- Message handler: `app/A2A/{Name}MessageHandler.php`
- Agent card provider: `app/A2A/{Name}AgentCardProvider.php`

**Example:**
```bash
php artisan make:a2a DataAnalyst
```

Generates:
- `App\A2A\DataAnalystServer`
- `App\A2A\DataAnalystTaskRepository`
- `App\A2A\DataAnalystMessageHandler`
- `App\A2A\DataAnalystAgentCardProvider`

The command also provides next steps in the output, including the exact route registration code.

## Detailed Implementation Guide

### Task Repository

Implement `TaskRepositoryInterface` to persist tasks:

```php
namespace App\Repositories;

use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Model\Task;

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

    public function findAll(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        // Implement querying logic
    }

    public function count(array $filters = []): int
    {
        // Implement count logic
    }

    public function generateTaskId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }

    public function generateContextId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }
}
```

### Message Handler (AI Agent Logic)

This is where you implement your actual AI agent:

```php
namespace App\Handlers;

use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Model\Task;
use NeuronCore\A2A\Model\Message;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Model\Artifact;
use NeuronCore\A2A\Model\TaskStatus;
use NeuronCore\A2A\Enum\TaskState;
use OpenAI\Client;

class DataAnalysisHandler implements MessageHandlerInterface
{
    public function __construct(
        protected Client $openai,
    ) {}

    public function handle(Task $task, array $messages): Task
    {
        $history = array_merge($task->history ?? [], $messages);

        // Extract user query
        $lastMessage = end($messages);
        $userText = $this->extractText($lastMessage);

        // Call AI service (OpenAI, Claude, etc.)
        $response = $this->openai->chat()->create([
            'model' => 'gpt-4',
            'messages' => $this->convertToOpenAIFormat($history),
        ]);

        // Create agent response
        $agentMessage = new Message(
            role: 'agent',
            parts: [new TextPart($response->choices[0]->message->content)]
        );

        $history[] = $agentMessage;

        // Create artifact with result
        $artifact = new Artifact(
            id: uniqid('artifact_'),
            parts: [new TextPart($response->choices[0]->message->content)]
        );

        // Return completed task
        return new Task(
            id: $task->id,
            contextId: $task->contextId,
            status: new TaskStatus(
                state: TaskState::COMPLETED,
                message: new TextPart('Analysis completed')
            ),
            history: $history,
            artifacts: [$artifact],
        );
    }
}
```

### Agent Card

Define your agent's capabilities:

```php
namespace App\A2A;

use NeuronCore\A2A\Model\AgentCard\AgentCard;
use NeuronCore\A2A\Model\AgentCard\AgentProvider;
use NeuronCore\A2A\Model\AgentCard\AgentSkill;

class DataAnalystAgentCard
{
    public function get(): AgentCard
    {
        return new AgentCard(
            protocolVersion: '0.3.0',
            name: 'Data Analyst Agent',
            description: 'Specialized in data analysis and statistics',
            url: url('/a2a/data-analyst'),
            preferredTransport: 'JSONRPC',
            version: '1.0.0',
            provider: new AgentProvider(
                organization: config('app.name'),
                url: config('app.url'),
            ),
            skills: [
                new AgentSkill(
                    id: 'data-analysis',
                    name: 'Data Analysis',
                    description: 'Analyze datasets and provide insights',
                    tags: ['data', 'statistics'],
                    examples: ['Analyze this sales data', 'Calculate statistics'],
                    inputModes: ['text/plain', 'application/json'],
                    outputModes: ['text/plain', 'application/json'],
                ),
            ],
        );
    }
}
```

## Testing Your Agents

```bash
# Get agent card
curl http://localhost/a2a/data-analyst/.well-known/agent-card.json

# Send a message
curl -X POST http://localhost/a2a/data-analyst \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "message/send",
    "params": {
      "messages": [{
        "role": "user",
        "parts": [{"kind": "text", "text": "Analyze this data"}]
      }]
    }
  }'
```

## Benefits of This Architecture

✅ **Multiple Agents** - Host many agents in one Laravel app
✅ **Independent Configuration** - Each agent has its own middleware, rate limits
✅ **Clean Encapsulation** - Each agent is self-contained
✅ **Laravel DI Compatible** - Use dependency injection in factory methods
✅ **Easy Testing** - Test each agent independently
✅ **Flexible** - Mix different AI services, repositories per agent
