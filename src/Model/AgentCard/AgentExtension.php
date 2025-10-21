<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\AgentCard;

class AgentExtension
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $id,
        public array $data = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            ...$this->data,
        ];
    }
}
