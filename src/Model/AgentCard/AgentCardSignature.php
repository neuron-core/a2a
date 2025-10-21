<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\AgentCard;

class AgentCardSignature
{
    /**
     * @param array<string, mixed>|null $header
     */
    public function __construct(
        public string $protected,
        public string $signature,
        public ?array $header = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'protected' => $this->protected,
            'signature' => $this->signature,
        ];

        if ($this->header !== null) {
            $data['header'] = $this->header;
        }

        return $data;
    }
}
