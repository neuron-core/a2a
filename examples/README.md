# A2A PHP Server Examples

This directory contains examples showing how to use the A2A PHP server.

## Running the Example

```bash
php examples/a2a.php
```

## Example Overview

The `a2a.php` file demonstrates:

1. **Creating a Message Handler** - Implements `MessageHandlerInterface` to process incoming messages
2. **Setting up an Agent Card Provider** - Implements `AgentCardProviderInterface` to define agent capabilities
3. **Creating HTTP Adapters** - Implements `HttpRequestInterface` and `HttpResponseInterface` for framework-agnostic HTTP handling
4. **Initializing the Server** - Sets up the A2A server with all required dependencies
5. **Handling Requests** - Shows three examples:
   - Getting the agent card
   - Sending a message
   - Listing tasks

## Integration with Your Framework

To integrate with your preferred PHP framework:

### 1. Implement the HTTP Interfaces

Create adapters for your framework's request/response objects:

```php
class LaravelHttpRequest implements HttpRequestInterface {
    public function __construct(
        protected \Illuminate\Http\Request $request
    ) {}

    public function getMethod(): string {
        return $this->request->method();
    }

    // ... implement other methods
}
```

### 2. Implement Required Interfaces

- **TaskRepositoryInterface** - Store and retrieve tasks (use database, Redis, etc.)
- **MessageHandlerInterface** - Process messages and generate responses
- **AgentCardProviderInterface** - Define your agent's capabilities

### 3. Create and Use the Server

```php
$server = new A2AServer(
    taskRepository: new YourTaskRepository(),
    messageHandler: new YourMessageHandler(),
    agentCardProvider: new YourAgentCardProvider(),
);

$httpHandler = new A2AHttpHandler(
    server: $server,
    agentCardProvider: $agentCardProvider,
);

// In your route handler:
$httpHandler->handle($request, $response);
```

## A2A Protocol Methods Supported

- `message/send` - Send messages and receive responses
- `tasks/get` - Retrieve a specific task
- `tasks/list` - List all tasks with filtering
- `tasks/cancel` - Cancel a running task
- `agent/getAuthenticatedExtendedCard` - Get agent capabilities

## Agent Card Endpoint

The server automatically serves the agent card at:
- `/.well-known/agent-card.json`
