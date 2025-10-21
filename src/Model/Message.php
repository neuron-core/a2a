<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model;

use NeuronCore\A2A\Model\Part\PartInterface;

class Message
{
    /**
     * @param string $role Either "user" or "agent"
     * @param array<PartInterface> $parts
     */
    public function __construct(
        public string $role,
        public array $parts,
    ) {
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'parts' => \array_map(fn (PartInterface $part): array => $part->toArray(), $this->parts),
        ];
    }
}
