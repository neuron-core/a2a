<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Laravel;

use Illuminate\Http\JsonResponse;
use NeuronCore\A2A\Http\HttpResponseInterface;

class LaravelHttpResponse implements HttpResponseInterface
{
    protected int $statusCode = 200;
    protected array $headers = [];
    protected string $body = '';

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function send(): void
    {
        $data = \json_decode($this->body, true);

        $response = new JsonResponse($data, $this->statusCode);

        foreach ($this->headers as $name => $value) {
            $response->header($name, $value);
        }
    }
}
