<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Http;

interface HttpRequestInterface
{
    public function getMethod(): string;

    public function getPath(): string;

    public function getBody(): string;

    public function getHeader(string $name): ?string;
}
