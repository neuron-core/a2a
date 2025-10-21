<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Http\Laravel;

use Illuminate\Support\Facades\Route;

final class A2A
{
    public static function routes(string $path = '/a2a'): void
    {
        Route::post($path, A2AController::class)
            ->name('a2a.handle');

        Route::get('/.well-known/agent-card.json', A2AController::class)
            ->name('a2a.agent-card');
    }
}
