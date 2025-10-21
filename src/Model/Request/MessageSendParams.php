<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\Request;

class MessageSendParams
{
    /**
     * @param array<mixed> $messages
     * @param array<string, mixed>|null $config
     */
    public function __construct(
        public ?string $taskId,
        public array $messages,
        public ?array $config = null,
    ) {
    }
}
