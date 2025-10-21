<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\AgentCard;

class AgentInterface
{
    public function __construct(
        public string $url,
        public string $transport,
    ) {
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'transport' => $this->transport,
        ];
    }
}
