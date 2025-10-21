# Laravel A2A Artisan Commands

## make:a2a Command

Generate a complete A2A agent server with all required components.

### Usage

```bash
php artisan make:a2a {name}
```

### Example

```bash
php artisan make:a2a DataAnalyst
```

**Output:**
```
A2A Server created successfully!

Generated files:
  - App\A2A\DataAnalystServer
  - App\A2A\DataAnalystTaskRepository
  - App\A2A\DataAnalystMessageHandler
  - App\A2A\DataAnalystAgentCard

Next steps:
  1. Implement your AI logic in DataAnalystMessageHandler
  2. Configure agent capabilities in DataAnalystAgentCard
  3. Register route in routes/api.php:

     A2A::route('/a2a/data-analyst', \App\A2A\DataAnalystServer::class);
```

### Generated Files

#### 1. Server Class (`{Name}Server.php`)

The main server class that extends `A2AServer` and wires up all components:

```php
class DataAnalystServer extends A2AServer
{
    protected function taskRepository(): TaskRepositoryInterface
    {
        return app(DataAnalystTaskRepository::class);
    }

    protected function messageHandler(): MessageHandlerInterface
    {
        return app(DataAnalystMessageHandler::class);
    }

    protected function agentCard(): AgentCard
    {
        return app(DataAnalystAgentCard::class)->get();
    }
}
```

#### 2. Task Repository (`{Name}TaskRepository.php`)

Handles task persistence with TODO comments for database implementation:

```php
class DataAnalystTaskRepository implements TaskRepositoryInterface
{
    // In-memory storage by default
    // TODO comments guide you to add database persistence

    public function save(Task $task): void { ... }
    public function find(string $taskId): ?Task { ... }
    public function findAll(array $filters = [], ?int $limit = null, ?int $offset = null): array { ... }
    public function count(array $filters = []): int { ... }
    public function generateTaskId(): string { ... }
    public function generateContextId(): string { ... }
}
```

#### 3. Message Handler (`{Name}MessageHandler.php`)

**This is where you implement your AI agent logic:**

```php
class DataAnalystMessageHandler implements MessageHandlerInterface
{
    /**
     * Handle incoming messages and generate AI agent responses
     */
    public function handle(Task $task, array $messages): Task
    {
        // TODO: Integrate with OpenAI, Claude, or other AI services
        // Example code included in comments

        // Returns a completed Task with agent response and artifacts
    }
}
```

#### 4. Agent Card (`{Name}AgentCard.php`)

Defines your agent's identity and capabilities:

```php
class DataAnalystAgentCard
{
    public function get(): AgentCard
    {
        return new AgentCard(
            name: 'DataAnalyst Agent',
            description: 'TODO: Describe what your agent does',
            url: url('/a2a/data-analyst'),
            skills: [
                // TODO: Define your agent's skills
            ],
            // ...
        );
    }
}
```

### After Generation

1. **Implement AI Logic**: Open `{Name}MessageHandler.php` and add your AI service integration
2. **Update Agent Card**: Edit `{Name}AgentCard.php` to describe your agent's capabilities
3. **Register Route**: Add to `routes/api.php`:
   ```php
   A2A::route('/a2a/{kebab-name}', App\A2A\{Name}Server::class);
   ```

### Multiple Agents

Generate as many agents as you need:

```bash
php artisan make:a2a DataAnalyst
php artisan make:a2a Translator
php artisan make:a2a CodeGenerator
```

Each agent is completely independent with its own:
- Server implementation
- Task storage
- Message handler (AI logic)
- Agent card (capabilities)
- Route and middleware

### Benefits

✅ **Quick Setup** - Generate complete agent in seconds
✅ **Best Practices** - Pre-configured with proper structure
✅ **TODO Comments** - Guides you through implementation
✅ **Laravel Integration** - Uses dependency injection and conventions
✅ **Type Safe** - Full PHP 8.1+ type hints
✅ **Ready to Customize** - Easy to modify generated code
