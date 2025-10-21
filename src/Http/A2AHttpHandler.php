<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Http;

use NeuronCore\A2A\Contract\AgentCardProviderInterface;
use NeuronCore\A2A\JsonRpc\JsonRpcRequest;
use NeuronCore\A2A\Server\A2AServer;

class A2AHttpHandler
{
    public function __construct(
        protected A2AServer $server,
        protected AgentCardProviderInterface $agentCardProvider,
    ) {
    }

    public function handle(HttpRequestInterface $request, HttpResponseInterface $response): void
    {
        $response->setHeader('Content-Type', 'application/json');

        if ($request->getPath() === '/.well-known/agent-card.json' && $request->getMethod() === 'GET') {
            $this->handleAgentCard($response);
            return;
        }

        if ($request->getMethod() !== 'POST') {
            $this->sendError($response, 405, 'Method not allowed');
            return;
        }

        $body = $request->getBody();

        if ($body === '' || $body === '0') {
            $this->sendError($response, 400, 'Empty request body');
            return;
        }

        try {
            $data = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->sendError($response, 400, 'Invalid JSON: ' . $e->getMessage());
            return;
        }

        $jsonRpcRequest = JsonRpcRequest::fromArray($data);
        $jsonRpcResponse = $this->server->handleRequest($jsonRpcRequest);

        $response
            ->setStatusCode(200)
            ->setBody(\json_encode($jsonRpcResponse->toArray(), \JSON_THROW_ON_ERROR))
            ->send();
    }

    protected function handleAgentCard(HttpResponseInterface $response): void
    {
        $agentCard = $this->agentCardProvider->getAgentCard();

        $response
            ->setStatusCode(200)
            ->setBody(\json_encode($agentCard->toArray(), \JSON_THROW_ON_ERROR))
            ->send();
    }

    protected function sendError(HttpResponseInterface $response, int $code, string $message): void
    {
        $response
            ->setStatusCode($code)
            ->setBody(\json_encode(['error' => $message], \JSON_THROW_ON_ERROR))
            ->send();
    }
}
