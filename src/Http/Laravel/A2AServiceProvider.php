<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Http\Laravel;

use Illuminate\Support\ServiceProvider;
use NeuronCore\A2A\Contract\AgentCardProviderInterface;
use NeuronCore\A2A\Contract\MessageHandlerInterface;
use NeuronCore\A2A\Contract\TaskRepositoryInterface;
use NeuronCore\A2A\Http\A2AHttpHandler;
use NeuronCore\A2A\Server\A2AServer;

final class A2AServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(A2AServer::class, function ($app) {
            return new A2AServer(
                taskRepository: $app->make(TaskRepositoryInterface::class),
                messageHandler: $app->make(MessageHandlerInterface::class),
                agentCardProvider: $app->make(AgentCardProviderInterface::class),
            );
        });

        $this->app->singleton(A2AHttpHandler::class, function ($app) {
            return new A2AHttpHandler(
                server: $app->make(A2AServer::class),
                agentCardProvider: $app->make(AgentCardProviderInterface::class),
            );
        });
    }
}
