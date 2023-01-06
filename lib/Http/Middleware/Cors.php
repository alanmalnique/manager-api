<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Aeatech\Router\Middleware\RouteMiddleware;
use Aeatech\Router\Request\Request;
use Aeatech\Router\Response\Response;
use Closure;

class Cors implements RouteMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $request->setHeader('X-Requested-With', 'XMLHttpRequest');

        $response = $next($request);

        $response->setHeader('Access-Control-Allow-Origin' , '*');
        $response->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE, PATCH');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, api-token, Accept, Authorization, X-Requested-With, Application');

        return $response;
    }
}