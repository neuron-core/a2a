<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Model\File;

class FileWithUri implements FileInterface
{
    public function __construct(
        public string $uri,
        public string $fileName,
        public string $mimeType,
    ) {
    }

    public function toArray(): array
    {
        return [
            'uri' => $this->uri,
            'fileName' => $this->fileName,
            'mimeType' => $this->mimeType,
        ];
    }
}
