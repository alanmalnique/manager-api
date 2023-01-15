<?php

declare(strict_types=1);

namespace App\Provider;

use Aeatech\Router\Response\Response;

final class KernelProvider
{
    private static array $providers = [
        \Aeatech\Jwt\Provider\JWTProvider::class,
        \Aeatech\Database\Provider\DatabaseProvider::class,
    ];

    public static function boot(): void
    {
        try {
            DatabaseProvider::boot();
            RouterProvider::boot();
        } catch (\BadFunctionCallException $exception) {
            $response = Response::json(['message' => 'Method not found or malformed.'], 404);
            $response::getResponse()->send();
        } catch (\Aeatech\Router\Exception\ClassNotFound $exception) {
            $response = Response::json(['message' => 'Class not found or malformed.'], 404);
            $response::getResponse()->send();
        } catch (\Aeatech\Router\Exception\RouteNotFound $exception) {
            $response = Response::json(['message' => 'Route not found or malformed.'], 404);
            $response::getResponse()->send();
        } catch (\Aeatech\Router\Exception\MethodNotAllowed $exception) {
            $response = Response::json(['message' => 'Method not allowed.'], 403);
            $response::getResponse()->send();
        } catch (\Aeatech\Router\Exception\RouteException $exception) {
            $response = Response::json(['message' => $exception->getMessage()], $exception->getCode());
            $response::getResponse()->send();
        } catch (\Throwable $exception) {
            $response = ['message' => $exception->getMessage(), 'trace' => $exception->getTrace()];
            \Aeatech\Router\Response\Log\ResponseLogger::log($response);
            $response = Response::json($response, $exception->getCode() < 600 ? $exception->getCode(): 500);
            $response::getResponse()->send();
        }
    }

    public static function publish(): void
    {
        foreach (self::$providers as $provider) {
            (new $provider())->publish();
        }
    }
}
