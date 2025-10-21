<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\Part;

class TextPart implements PartInterface
{
    public function __construct(
        public string $text,
    ) {
    }

    public function toArray(): array
    {
        return [
            'kind' => 'text',
            'text' => $this->text,
        ];
    }
}
