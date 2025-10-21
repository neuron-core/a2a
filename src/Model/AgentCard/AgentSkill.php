<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\AgentCard;

class AgentSkill
{
    /**
     * @param array<string> $tags
     * @param array<string> $examples
     * @param array<string> $inputModes
     * @param array<string> $outputModes
     * @param array<array<string, mixed>>|null $security
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public array $tags = [],
        public array $examples = [],
        public array $inputModes = [],
        public array $outputModes = [],
        public ?array $security = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'tags' => $this->tags,
            'examples' => $this->examples,
            'inputModes' => $this->inputModes,
            'outputModes' => $this->outputModes,
        ];

        if ($this->security !== null) {
            $data['security'] = $this->security;
        }

        return $data;
    }
}
