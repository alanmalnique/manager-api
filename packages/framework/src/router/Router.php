<?php

declare(strict_types=1);

namespace Aeatech\Router;

use Aeatech\Router\Exception\MethodNotAllowed;
use Aeatech\Router\Exception\RouteNotFound;
use Symfony\Component\HttpFoundation\Request;

final class Router implements RouterInterface
{
    private \ArrayObject $routes;
    private RouterGenerator $urlGenerator;

    /**
     * Router constructor.
     * @param $routes array<RouteBase>
     */
    public function __construct(array $routes = [], string $prefix = '')
    {
        $this->routes = new \ArrayObject();
        $this->urlGenerator = new RouterGenerator($this->routes, $prefix);
        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    private function add(RouteBase $route): self
    {
        $this->routes->offsetSet($route->getName(), $route);
        return $this;
    }

    public function match(Request $serverRequest): RouteBase
    {
        return $this->matchFromPath($serverRequest->getPathInfo(), $serverRequest->getMethod());
    }

    public function matchFromPath(string $path, string $method): RouteBase
    {
        /** @var RouteBase $route */
        foreach ($this->routes as $route) {
            if ($route->match($path) === false) {
                continue;
            }

            if (!in_array($method, $route->getMethods())) {
                throw new MethodNotAllowed();
            }
            return $route;
        }

        throw new RouteNotFound();
    }

    public function generateUri(string $name, array $parameters = [], bool $absoluteUrl = false): string
    {
        return $this->urlGenerator->generate($name, $parameters, $absoluteUrl);
    }
}