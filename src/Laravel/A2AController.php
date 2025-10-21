<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Laravel;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use NeuronCore\A2A\JsonRpc\JsonRpcRequest;
use NeuronCore\A2A\Server\A2AServer;

final class A2AController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $serverClass = $request->route('serverClass');

        if (!$serverClass || !\is_subclass_of($serverClass, A2AServer::class)) {
            return new JsonResponse(['error' => 'Invalid server configuration'], 500);
        }

        $server = app($serverClass);

        $body = $request->getContent();

        if ($body === '' || $body === '0') {
            return new JsonResponse(['error' => 'Empty request body'], 400);
        }

        try {
            $data = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new JsonResponse(['error' => 'Invalid JSON: ' . $e->getMessage()], 400);
        }

        $jsonRpcRequest = JsonRpcRequest::fromArray($data);
        $jsonRpcResponse = $server->handleRequest($jsonRpcRequest);

        return new JsonResponse($jsonRpcResponse->toArray());
    }

    public function handleAgentCard(Request $request): JsonResponse
    {
        $serverClass = $request->route('serverClass');

        if (!$serverClass || !\is_subclass_of($serverClass, A2AServer::class)) {
            return new JsonResponse(['error' => 'Invalid server configuration'], 500);
        }

        $server = app($serverClass);

        return new JsonResponse($server->getAgentCard());
    }
}
