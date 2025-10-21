<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model;

class Task
{
    /**
     * @param array<Message>|null $history
     * @param array<Artifact>|null $artifacts
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        public string $id,
        public string $contextId,
        public TaskStatus $status,
        public ?array $history = null,
        public ?array $artifacts = null,
        public ?array $metadata = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'kind' => 'task',
            'id' => $this->id,
            'contextId' => $this->contextId,
            'status' => $this->status->toArray(),
        ];

        if ($this->history !== null) {
            $data['history'] = \array_map(fn (Message $message): array => $message->toArray(), $this->history);
        }

        if ($this->artifacts !== null) {
            $data['artifacts'] = \array_map(fn (Artifact $artifact): array => $artifact->toArray(), $this->artifacts);
        }

        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }
}
