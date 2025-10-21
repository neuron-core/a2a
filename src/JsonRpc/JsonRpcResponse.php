<?php

declare(strict_types=1);

namespace NeuronCore\A2A\JsonRpc;

class JsonRpcResponse
{
    public function __construct(
        public mixed $result,
        public string|int|null $id = null,
        public string $jsonrpc = '2.0',
    ) {
    }

    public function toArray(): array
    {
        return [
            'jsonrpc' => $this->jsonrpc,
            'id' => $this->id,
            'result' => $this->result,
        ];
    }
}
