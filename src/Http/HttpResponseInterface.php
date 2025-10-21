<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Http;

interface HttpResponseInterface
{
    public function setStatusCode(int $code): self;

    public function setHeader(string $name, string $value): self;

    public function setBody(string $body): self;

    public function send(): void;
}
