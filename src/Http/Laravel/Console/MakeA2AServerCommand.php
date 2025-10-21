<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Http\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

final class MakeA2AServerCommand extends GeneratorCommand
{
    protected $signature = 'make:a2a {name : The name of the A2A server}';

    protected $description = 'Create a new A2A server with all required components';

    protected $type = 'A2A Server';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/a2a-server.stub';
    }

    // @phpstan-ignore-next-line
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\A2A';
    }

    // @phpstan-ignore-next-line
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $serverName = $this->getNameInput();
        $serverClass = Str::studly($serverName);

        return \str_replace(
            ['{{ serverName }}', '{{ serverClass }}'],
            [$serverName, $serverClass],
            $stub
        );
    }

    public function handle(): bool
    {
        $result = parent::handle();

        if ($result === false) {
            return false;
        }

        $this->generateTaskRepository();
        $this->generateMessageHandler();
        $this->generateAgentCard();

        $this->displaySuccessMessage();

        return true;
    }

    protected function generateTaskRepository(): void
    {
        $name = $this->getNameInput() . 'TaskRepository';
        $path = $this->getPath($this->qualifyClass($name));

        $this->makeDirectory($path);

        $stub = $this->files->get(__DIR__ . '/stubs/a2a-repository.stub');
        $stub = $this->replaceNamespace($stub, $this->qualifyClass($name))
            ->replaceClass($stub, $name);

        $stub = \str_replace(
            '{{ serverName }}',
            $this->getNameInput(),
            $stub
        );

        $this->files->put($path, $stub);
    }

    protected function generateMessageHandler(): void
    {
        $name = $this->getNameInput() . 'MessageHandler';
        $path = $this->getPath($this->qualifyClass($name));

        $this->makeDirectory($path);

        $stub = $this->files->get(__DIR__ . '/stubs/a2a-handler.stub');
        $stub = $this->replaceNamespace($stub, $this->qualifyClass($name))
            ->replaceClass($stub, $name);

        $stub = \str_replace(
            '{{ serverName }}',
            $this->getNameInput(),
            $stub
        );

        $this->files->put($path, $stub);
    }

    protected function generateAgentCard(): void
    {
        $name = $this->getNameInput() . 'AgentCard';
        $path = $this->getPath($this->qualifyClass($name));

        $this->makeDirectory($path);

        $stub = $this->files->get(__DIR__ . '/stubs/a2a-card-provider.stub');
        $stub = $this->replaceNamespace($stub, $this->qualifyClass($name))
            ->replaceClass($stub, $name);

        $serverName = $this->getNameInput();
        $stub = \str_replace(
            ['{{ serverName }}', '{{ serverNameLower }}'],
            [$serverName, Str::kebab($serverName)],
            $stub
        );

        $this->files->put($path, $stub);
    }

    protected function displaySuccessMessage(): void
    {
        $serverName = $this->getNameInput();
        $serverClass = Str::studly($serverName) . 'Server';
        $namespace = $this->getDefaultNamespace($this->laravel->getNamespace());

        $this->info('');
        $this->info('A2A Server created successfully!');
        $this->info('');
        $this->info('Generated files:');
        $this->line("  - {$namespace}\\{$serverClass}");
        $this->line("  - {$namespace}\\{$serverName}TaskRepository");
        $this->line("  - {$namespace}\\{$serverName}MessageHandler");
        $this->line("  - {$namespace}\\{$serverName}AgentCard");
        $this->info('');
        $this->info('Next steps:');
        $this->line('  1. Implement your AI logic in ' . $serverName . 'MessageHandler');
        $this->line('  2. Configure agent capabilities in ' . $serverName . 'AgentCard');
        $this->line('  3. Register route in routes/api.php:');
        $this->info('');
        $this->line("     A2A::route('/a2a/" . Str::kebab($serverName) . "', \\{$namespace}\\{$serverClass}::class);");
        $this->info('');
    }
}
