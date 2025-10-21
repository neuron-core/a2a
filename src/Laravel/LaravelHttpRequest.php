<?php

declare(strict_types=1);

namespace NeuronCore\A2A\Laravel;

use Illuminate\Http\Request;
use NeuronCore\A2A\Http\HttpRequestInterface;

class LaravelHttpRequest implements HttpRequestInterface
{
    public function __construct(
        protected Request $request,
    ) {
    }

    public function getMethod(): string
    {
        return $this->request->method();
    }

    public function getPath(): string
    {
        return $this->request->path();
    }

    public function getBody(): string
    {
        return $this->request->getContent();
    }

    public function getHeader(string $name): ?string
    {
        return $this->request->header($name);
    }
}
