<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\File;

class FileWithBytes implements FileInterface
{
    public function __construct(
        public string $bytes,
        public string $fileName,
        public string $mimeType,
    ) {
    }

    public function toArray(): array
    {
        return [
            'bytes' => $this->bytes,
            'fileName' => $this->fileName,
            'mimeType' => $this->mimeType,
        ];
    }
}
