<?php

declare(strict_types=1);

namespace Aeatech\Router\Middleware;

use Aeatech\Router\Response\Response;
use Closure;
use Aeatech\Router\Request\Request;

interface RouteMiddleware
{
    public function handle(Request $request, Closure $next): Response;
}