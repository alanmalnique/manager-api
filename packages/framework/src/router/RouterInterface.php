<?php

declare(strict_types=1);

namespace Aeatech\Router;

use Aeatech\Router\Exception\RouteNotFound;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

interface RouterInterface
{
    /**
     * @param Request $serverRequest
     * @return RouteBase
     * @throws RouteNotFound if no found route.
     */
    public function match(Request $serverRequest): RouteBase;

    /**
     * @param string $path
     * @param string $method
     * @return RouteBase
     * @throws RouteNotFound if no found route.
     */
    public function matchFromPath(string $path, string $method): RouteBase;

    /**
     * @param string $name
     * @param array $parameters
     * @return string
     * @throws \InvalidArgumentException if unable to generate the given URI.
     */
    public function generateUri(string $name, array $parameters = []): string;
}