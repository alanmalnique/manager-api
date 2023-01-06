<?php

declare(strict_types=1);

namespace App\Provider;

use Aeatech\Router\Provider\RouterProvider as Provider;

final class RouterProvider extends Provider
{
    protected static string $namespace = 'App\Http';

    protected static string $prefix = '/api';

    protected static array $routeMiddleware = [
        \App\Http\Middleware\Cors::class
    ];

    public static function boot(): void
    {
        self::setNamespace(self::$namespace);
        self::setPrefix(self::$prefix);
        self::setRouteMiddleware(self::$routeMiddleware);
        self::map();
        parent::boot();
    }

    private static function map(): void
    {
        require(__DIR__.'./../../routes/api.php');
    }
}
