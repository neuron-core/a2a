<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Laravel;

use Illuminate\Support\ServiceProvider;
use NeuronCore\A2A\Laravel\Console\MakeA2AServerCommand;

class A2AServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Each A2AServer concrete implementation will be resolved automatically
        // by Laravel's container when routes are hit. No global bindings needed.
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeA2AServerCommand::class,
            ]);
        }
    }
}
