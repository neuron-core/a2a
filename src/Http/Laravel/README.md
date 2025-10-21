# Laravel A2A Integration

This package provides Laravel-specific adapters for the A2A protocol server.

## Installation

1. **Register the Service Provider** in `config/app.php`:

```php
'providers' => [
    // ...
    NeuronCore\A2A\Http\Laravel\A2AServiceProvider::class,
],
```

Or if using Laravel 11+ with auto-discovery, it will be registered automatically.

2. **Implement the Required Interfaces**

You must implement three interfaces to complete the integration:

### TaskRepositoryInterface

Store and retrieve tasks (use Eloquent, Redis, etc.):

```php
namespace App\A2A;

use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Model\Task;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function save(Task $task): void
    {
        // Store task in database
    }

    public function find(string $taskId): ?Task
    {
        // Retrieve task from database
    }

    public function findAll(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        // Query tasks with filters
    }

    public function count(array $filters = []): int
    {
        // Count tasks
    }

    public function generateTaskId(): string
    {
        return \Illuminate\Support\Str::uuid()->toString();
    }

    public function generateContextId(): string
    {
        return \Illuminate\Support\Str::uuid()->toString();
    }
}
```

### MessageHandlerInterface

Process messages and call your AI service:

```php
namespace App\A2A;

use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Enum\TaskState;
use NeuronCore\A2A\Model\Artifact;
use NeuronCore\A2A\Model\Message;
use NeuronCore\A2A\Model\Part\TextPart;
use NeuronCore\A2A\Model\Task;
use NeuronCore\A2A\Model\TaskStatus;
use OpenAI\Client;

class OpenAIMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        protected Client $openai,
    ) {
    }

    public function handle(Task $task, array $messages): Task
    {
        $history = array_merge($task->history ?? [], $messages);

        // Call OpenAI API
        $response = $this->openai->chat()->create([
            'model' => 'gpt-4',
            'messages' => $this->convertToOpenAIFormat($history),
        ]);

        // Create agent response
        $agentMessage = new Message(
            role: 'agent',
            parts: [new TextPart($response->choices[0]->message->content)],
        );

        $history[] = $agentMessage;

        // Create artifact
        $artifact = new Artifact(
            id: uniqid('artifact_'),
            parts: [new TextPart($response->choices[0]->message->content)],
        );

        // Return completed task
        return new Task(
            id: $task->id,
            contextId: $task->contextId,
            status: new TaskStatus(
                state: TaskState::COMPLETED,
                message: new TextPart('AI response generated'),
            ),
            history: $history,
            artifacts: [$artifact],
            metadata: $task->metadata,
        );
    }

    protected function convertToOpenAIFormat(array $history): array
    {
        // Convert A2A messages to OpenAI format
    }
}
```

### AgentCardProviderInterface

Define your agent's capabilities:

```php
namespace App\A2A;

use NeuronCore\A2A\Contract\AgentCardProviderInterface;
use NeuronCore\A2A\Model\AgentCard\AgentCard;
use NeuronCore\A2A\Model\AgentCard\AgentProvider;
use NeuronCore\A2A\Model\AgentCard\AgentSkill;

class MyAgentCardProvider implements AgentCardProviderInterface
{
    public function getAgentCard(): AgentCard
    {
        return new AgentCard(
            protocolVersion: '0.3.0',
            name: config('a2a.agent.name'),
            description: config('a2a.agent.description'),
            url: config('app.url') . '/a2a',
            preferredTransport: 'JSONRPC',
            version: '1.0.0',
            provider: new AgentProvider(
                organization: config('a2a.organization.name'),
                url: config('a2a.organization.url'),
            ),
            skills: [
                new AgentSkill(
                    id: 'general-assistant',
                    name: 'General Assistant',
                    description: 'Helps with various tasks',
                    tags: ['assistant', 'general'],
                    examples: ['Help me write an email', 'Analyze this data'],
                    inputModes: ['text/plain'],
                    outputModes: ['text/plain'],
                ),
            ],
        );
    }
}
```

3. **Bind Your Implementations** in `App\Providers\AppServiceProvider`:

```php
use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Contract\AgentCardProviderInterface;
use App\A2A\EloquentTaskRepository;
use App\A2A\OpenAIMessageHandler;
use App\A2A\MyAgentCardProvider;

public function register(): void
{
    $this->app->singleton(TaskRepositoryInterface::class, EloquentTaskRepository::class);
    $this->app->singleton(MessageHandlerInterface::class, OpenAIMessageHandler::class);
    $this->app->singleton(AgentCardProviderInterface::class, MyAgentCardProvider::class);
}
```

4. **Register Routes** in `routes/api.php`:

```php
use NeuronCore\A2A\Http\Laravel\A2A;

A2A::routes('/a2a');
```

This registers:
- `POST /a2a` - JSON-RPC endpoint
- `GET /.well-known/agent-card.json` - Agent card endpoint

## Usage

Your A2A server is now available at:

```bash
# Send a message
curl -X POST http://localhost/api/a2a \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "message/send",
    "params": {
      "messages": [
        {
          "role": "user",
          "parts": [
            {
              "kind": "text",
              "text": "Hello!"
            }
          ]
        }
      ]
    }
  }'

# Get agent card
curl http://localhost/.well-known/agent-card.json
```

## Middleware

Apply Laravel middleware to your A2A routes:

```php
use NeuronCore\A2A\Http\Laravel\A2AController;

Route::middleware(['auth:api', 'throttle:60,1'])
    ->post('/a2a', A2AController::class);

Route::get('/.well-known/agent-card.json', A2AController::class);
```

## Error Handling

If you haven't bound the required interfaces, Laravel will throw a binding exception with a clear error message:

```
Target [NeuronCore\A2A\Contract\TaskRepositoryInterface] is not instantiable.
```

Make sure all three interfaces are bound in your service provider.
