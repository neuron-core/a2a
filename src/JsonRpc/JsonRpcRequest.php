<?php

declare(strict_types=1);

namespace NeuronCore\A2A\JsonRpc;

class JsonRpcRequest
{
    public function __construct(
        public string $jsonrpc,
        public string $method,
        public mixed $params,
        public string|int|null $id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            jsonrpc: $data['jsonrpc'] ?? '2.0',
            method: $data['method'],
            params: $data['params'] ?? null,
            id: $data['id'] ?? null,
        );
    }
}
