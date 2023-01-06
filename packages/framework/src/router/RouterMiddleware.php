<?php

declare(strict_types=1);

namespace Aeatech\Router;

use Aeatech\Router\RouterInterface;
use Aeatech\Router\Exception\MethodNotAllowed;
use Aeatech\Router\Exception\RouteNotFound;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class RouterMiddleware implements MiddlewareInterface
{
    public const CONTROLLER = 'controller';
    public const ACTION = 'action';
    public const NAME = 'name';

    private RouterInterface $router;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(RouterInterface $router, ResponseFactoryInterface $responseFactory)
    {
        $this->router = $router;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface  $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $route = $this->router->match($request);
            $routeHandler = $route->getHandler();
            $attributes = \array_merge([
                self::CONTROLLER => $routeHandler[0],
                self::ACTION => $routeHandler[1] ?? null,
                self::NAME => $route->getName(),
            ], $route->getAttributes());

            foreach ($attributes as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
        } catch (MethodNotAllowed $exception) {
            return $this->responseFactory->createResponse(405);
        } catch (RouteNotFound $exception) {
            return $this->responseFactory->createResponse(404);
        } catch (Throwable $exception) {
            throw $exception;
        }
        return $handler->handle($request);
    }
}