<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\AgentCard;

class AgentProvider
{
    public function __construct(
        public string $organization,
        public string $url,
    ) {
    }

    public function toArray(): array
    {
        return [
            'organization' => $this->organization,
            'url' => $this->url,
        ];
    }
}
