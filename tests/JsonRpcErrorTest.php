<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\JsonRpc\JsonRpcError;
use PHPUnit\Framework\TestCase;

class JsonRpcErrorTest extends TestCase
{
    public function test_creates_error_and_converts_to_array(): void
    {
        $error = new JsonRpcError(
            code: -32601,
            message: 'Method not found',
            id: 1
        );

        $array = $error->toArray();

        $this->assertSame('2.0', $array['jsonrpc']);
        $this->assertSame(1, $array['id']);
        $this->assertArrayHasKey('error', $array);
        $this->assertSame(-32601, $array['error']['code']);
        $this->assertSame('Method not found', $array['error']['message']);
    }

    public function test_creates_error_with_data(): void
    {
        $error = new JsonRpcError(
            code: -32603,
            message: 'Internal error',
            data: ['details' => 'Task not found'],
            id: 2
        );

        $array = $error->toArray();

        $this->assertSame(['details' => 'Task not found'], $array['error']['data']);
    }

    public function test_creates_error_without_data(): void
    {
        $error = new JsonRpcError(
            code: -32600,
            message: 'Invalid Request',
            id: 3
        );

        $array = $error->toArray();

        $this->assertArrayNotHasKey('data', $array['error']);
        $this->assertCount(2, $array['error']);
    }

    public function test_error_structure(): void
    {
        $error = new JsonRpcError(
            code: -32700,
            message: 'Parse error',
            id: null
        );

        $array = $error->toArray();

        $this->assertArrayHasKey('jsonrpc', $array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('error', $array);
        $this->assertCount(3, $array);
    }
}
