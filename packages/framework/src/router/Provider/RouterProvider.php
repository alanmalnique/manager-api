<?php

declare(strict_types=1);

namespace Aeatech\Router\Provider;

use Aeatech\Router\Exception\ClassNotFound;
use Aeatech\Router\Request\Request;
use Aeatech\Router\Response\Response;
use Aeatech\Router\Route;
use Aeatech\Router\RouteBase;
use Aeatech\Router\Router;

class RouterProvider
{
    protected static array $routeMiddleware = [];
    private static string $namespace;
    private static string $prefix = '';
    private static Router $router;

    /** @return string */
    public static function getNamespace(): string
    {
        return self::$namespace;
    }

    /** @param string $namespace */
    public static function setNamespace(string $namespace): void
    {
        self::$namespace = $namespace;
    }

    /** @param string $prefix */
    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }

    /** @return string */
    public static function getPrefix(): string
    {
        return self::$prefix;
    }

    /** @param array $routeMiddleware */
    public static function setRouteMiddleware(array $routeMiddleware): void
    {
        self::$routeMiddleware = $routeMiddleware;
    }

    public static function boot(): void
    {
        self::$router = (new Router(Route::getRoutes(), self::$prefix));
        $request = (new Request());
        $route = self::$router->match($request->request());
        self::processMiddlewares($route, $request);
    }

    private static function processMiddlewares(RouteBase $route, Request $request): void
    {
        $middlewares = self::getMiddlewares($route);
        if (count($middlewares) > 0) {
            foreach ($middlewares as $index => $middleware) {
                $class = new $middleware();
                $response = $class->handle($request, function () use ($route, $middlewares, $index, $request) {
                    if (count($middlewares) === ($index + 1)) {
                        return self::processRoute($route, $request);
                    }
                });
                $response::getResponse()->send();
            }
        } else {
            $response = self::processRoute($route, $request);
            $response::getResponse()->send();
        }
    }

    private static function getMiddlewares(RouteBase $route): array
    {
        return array_merge(self::$routeMiddleware, $route->getMiddlewares());
    }

    /** @throws ClassNotFound */
    private static function processRoute(RouteBase $route, Request $request): Response
    {
        $handler = self::getNamespace() . '\\' . $route->getHandler();
        $attributes = $route->getAttributes();
        [$className, $methodName] = explode("@", $handler);

        if (!class_exists($className)) {
            throw new ClassNotFound();
        }

        $controller = new $className();
        if (!method_exists($controller, $methodName)) {
            throw new \BadFunctionCallException();
        }

        return $controller->$methodName($request, ...array_values($attributes));
    }
}
