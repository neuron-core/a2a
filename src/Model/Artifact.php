<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model;

use NeuronCore\A2A\Model\Part\PartInterface;

class Artifact
{
    /**
     * @param array<PartInterface> $parts
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        public string $id,
        public array $parts,
        public ?array $metadata = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'parts' => \array_map(fn (PartInterface $part): array => $part->toArray(), $this->parts),
        ];

        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }
}
