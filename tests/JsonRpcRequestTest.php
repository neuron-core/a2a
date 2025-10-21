<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Tests;

use NeuronCore\A2A\JsonRpc\JsonRpcRequest;
use PHPUnit\Framework\TestCase;

class JsonRpcRequestTest extends TestCase
{
    public function test_creates_request_from_array(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'message/send',
            'params' => ['foo' => 'bar'],
            'id' => 1,
        ];

        $request = JsonRpcRequest::fromArray($data);

        $this->assertSame('2.0', $request->jsonrpc);
        $this->assertSame('message/send', $request->method);
        $this->assertSame(['foo' => 'bar'], $request->params);
        $this->assertSame(1, $request->id);
    }

    public function test_creates_request_with_string_id(): void
    {
        $data = [
            'method' => 'tasks/get',
            'params' => ['taskId' => '123'],
            'id' => 'abc-123',
        ];

        $request = JsonRpcRequest::fromArray($data);

        $this->assertSame('abc-123', $request->id);
    }

    public function test_creates_request_with_defaults(): void
    {
        $data = [
            'method' => 'tasks/list',
        ];

        $request = JsonRpcRequest::fromArray($data);

        $this->assertSame('2.0', $request->jsonrpc);
        $this->assertNull($request->params);
        $this->assertNull($request->id);
    }

    public function test_creates_notification_request_without_id(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'tasks/cancel',
            'params' => ['taskId' => '456'],
        ];

        $request = JsonRpcRequest::fromArray($data);

        $this->assertNull($request->id);
    }
}
