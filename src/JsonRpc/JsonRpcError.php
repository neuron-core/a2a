<?php

declare(strict_types=1);

namespace NeuronCore\A2A\JsonRpc;

final class JsonRpcError
{
    public function __construct(
        public int $code,
        public string $message,
        public mixed $data = null,
        public string|int|null $id = null,
        public string $jsonrpc = '2.0',
    ) {
    }

    public function toArray(): array
    {
        $error = [
            'code' => $this->code,
            'message' => $this->message,
        ];

        if ($this->data !== null) {
            $error['data'] = $this->data;
        }

        return [
            'jsonrpc' => $this->jsonrpc,
            'id' => $this->id,
            'error' => $error,
        ];
    }
}
