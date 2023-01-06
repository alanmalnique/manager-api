<?php

use Aeatech\Router\Response\Response;

require_once(__DIR__.'./../vendor/autoload.php');
require_once(__DIR__.'./../env.php');
require_once(__DIR__.'./../env.override.php');

\App\Provider\KernelProvider::boot();

$environment = getenv('ENVIRONMENT');

try {
    \App\Provider\RouterProvider::boot();
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
    $response = Response::json($response, $exception->getCode() ?: 500);
    $response::getResponse()->send();
}
