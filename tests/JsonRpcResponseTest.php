<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\JsonRpc\JsonRpcResponse;
use PHPUnit\Framework\TestCase;

class JsonRpcResponseTest extends TestCase
{
    public function test_creates_response_and_converts_to_array(): void
    {
        $response = new JsonRpcResponse(
            result: ['status' => 'ok'],
            id: 1
        );

        $array = $response->toArray();

        $this->assertSame('2.0', $array['jsonrpc']);
        $this->assertSame(1, $array['id']);
        $this->assertSame(['status' => 'ok'], $array['result']);
    }

    public function test_creates_response_with_null_id(): void
    {
        $response = new JsonRpcResponse(
            result: ['data' => 'test']
        );

        $array = $response->toArray();

        $this->assertNull($array['id']);
    }

    public function test_creates_response_with_string_id(): void
    {
        $response = new JsonRpcResponse(
            result: true,
            id: 'abc-123'
        );

        $array = $response->toArray();

        $this->assertSame('abc-123', $array['id']);
    }

    public function test_response_array_structure(): void
    {
        $response = new JsonRpcResponse(
            result: 42,
            id: 5
        );

        $array = $response->toArray();

        $this->assertArrayHasKey('jsonrpc', $array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('result', $array);
        $this->assertCount(3, $array);
    }
}
