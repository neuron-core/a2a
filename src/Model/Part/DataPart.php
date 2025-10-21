<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\Part;

class DataPart implements PartInterface
{
    public function __construct(
        public mixed $data,
        public string $mimeType = 'application/json',
    ) {
    }

    public function toArray(): array
    {
        return [
            'kind' => 'data',
            'data' => $this->data,
            'mimeType' => $this->mimeType,
        ];
    }
}
