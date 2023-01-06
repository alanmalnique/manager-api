<?php

declare(strict_types=1);

namespace Aeatech\Router\Request\Parser;

use Symfony\Component\HttpFoundation\Request;

final class RequestParser
{
    private Request $request;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    /** @return Request */
    public function getRequest(): Request
    {
        return $this->request;
    }

    public function all(): array
    {
        return array_merge_recursive($this->request->request->all(), $this->request->query->all(), $this->request->files->all());
    }

    public function files(): array
    {
        return $this->request->files->all();
    }

    public function get(): array
    {
        return $this->request->query->all();
    }

    public function post(): array
    {
        return $this->request->request->all();
    }

    public function headers(): array
    {
        return $this->request->headers->all();
    }

    public function header(string $name): string
    {
        return $this->request->headers->get($name);
    }

    public function setHeader(string $name, string $value): void
    {
        $this->request->headers->set($name, $value);
    }

    public function hasHeader(string $name): bool
    {
        return $this->request->headers->has($name);
    }
}
