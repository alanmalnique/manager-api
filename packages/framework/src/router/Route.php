<?php

declare(strict_types=1);

namespace Aeatech\Router;

use Aeatech\Router\Exception\MethodNotAllowed;

final class Route
{
    private static array $routes;

    /** @return array */
    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function get(string $name, string $path, mixed $handler, array $middlewares = [])
    {
        self::$routes[] = (new RouteBase($name, $path, $handler, $middlewares))->methods(['GET']);
    }

    public static function post(string $name, string $path, mixed $handler, array $middlewares = [])
    {
        self::$routes[] = (new RouteBase($name, $path, $handler, $middlewares))->methods(['GET']);
    }

    public static function put(string $name, string $path, mixed $handler, array $middlewares = [])
    {
        self::$routes[] = (new RouteBase($name, $path, $handler, $middlewares))->methods(['GET']);
    }

    public static function delete(string $name, string $path, mixed $handler, array $middlewares = [])
    {
        self::$routes[] = (new RouteBase($name, $path, $handler, $middlewares))->methods(['GET']);
    }

    /** @throws MethodNotAllowed */
    public static function resource(string $name, string $path, string $handler, array $methods, array $middlewares = [])
    {
        $requestMethods = [];
        foreach ($methods as $method) {
            $handler .= match ($method) {
                'index' => '@index',
                'show' => '@show',
                'delete' => '@delete',
                'update' => '@update',
                'create' => '@create',
                default => throw new MethodNotAllowed(),
            };
            $requestMethods[] = match ($method) {
                'index', 'show' => 'GET',
                'delete' => 'DELETE',
                'update' => 'PATCH',
                'create' => 'POST',
                default => throw new MethodNotAllowed(),
            };
            self::$routes[] = (new RouteBase($name, $path, $handler, $middlewares))->methods($requestMethods);
        }
    }
}
