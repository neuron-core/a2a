<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Http\Laravel;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use NeuronCore\A2A\Http\A2AHttpHandler;

final class A2AController extends Controller
{
    public function __construct(
        protected A2AHttpHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $a2aRequest = new LaravelHttpRequest($request);
        $a2aResponse = new LaravelHttpResponse();

        $this->handler->handle($a2aRequest, $a2aResponse);

        return $a2aResponse->send();
    }
}
