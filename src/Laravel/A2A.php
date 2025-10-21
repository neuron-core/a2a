<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Laravel;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;

final class A2A
{
    /**
     * @param class-string $serverClass
     */
    public static function route(string $path, string $serverClass): LaravelRoute
    {
        $agentCardPath = \rtrim($path, '/') . '/.well-known/agent-card.json';

        Route::get($agentCardPath, [A2AController::class, 'handleAgentCard'])
            ->defaults('serverClass', $serverClass);

        return Route::post($path, [A2AController::class, 'handle'])
            ->defaults('serverClass', $serverClass);
    }
}
