<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\Part;

use NeuronCore\A2A\Model\File\FileInterface;

class FilePart implements PartInterface
{
    public function __construct(
        public FileInterface $file,
        public string $mimeType,
    ) {
    }

    public function toArray(): array
    {
        return [
            'kind' => 'file',
            'file' => $this->file->toArray(),
            'mimeType' => $this->mimeType,
        ];
    }
}
