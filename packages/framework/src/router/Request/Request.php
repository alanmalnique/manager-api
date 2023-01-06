<?php

declare(strict_types=1);

namespace Aeatech\Router\Request;

use Aeatech\Router\Request\Parser\RequestParser;
use Psr\Http\Message\ServerRequestInterface;

final class Request
{
    private RequestParser $parser;

    public function __construct()
    {
        $this->parser = (new RequestParser());
    }

    public function all(): mixed
    {
        return $this->parser->all();
    }

    public function input(string $name, mixed $defaultValue = ''): mixed
    {
        return $this->parser->all()[$name] ?: $defaultValue;
    }

    public function files(string $name, mixed $defaultValue = []): array
    {
        return $this->parser->files()[$name] ?: $defaultValue;
    }

    public function get(string $name, mixed $defaultValue = []): array
    {
        return $this->parser->get()[$name] ?: $defaultValue;
    }

    public function post(string $name, mixed $defaultValue = []): array
    {
        return $this->parser->post()[$name] ?: $defaultValue;
    }

    public function headers(mixed $defaultValue = []): array
    {
        return $this->parser->headers() ?: $defaultValue;
    }

    public function header(string $name): string
    {
        return $this->parser->header($name);
    }

    public function setHeader(string $name, string $value): void
    {
        $this->parser->setHeader($name, $value);
    }

    public function hasHeader(string $name): bool
    {
        return $this->parser->hasHeader($name);
    }

    public function request(): \Symfony\Component\HttpFoundation\Request
    {
        return $this->parser->getRequest();
    }
}