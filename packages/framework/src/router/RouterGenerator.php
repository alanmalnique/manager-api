<?php

declare(strict_types=1);

namespace Aeatech\Router;

use Aeatech\Commons\Helper;
use ArrayAccess;
use InvalidArgumentException;
use function array_key_exists;
use function implode;
use function sprintf;
use function str_replace;
use function trim;

final class RouterGenerator
{
    private ArrayAccess $routes;
    private string $prefix;

    public function __construct(ArrayAccess $routes, string $prefix)
    {
        $this->routes = $routes;
        $this->prefix = $prefix;
    }

    public function generate(string $name, array $parameters = [], bool $absoluteUrl = false): string
    {
        if ($this->routes->offsetExists($name) === false) {
            throw new InvalidArgumentException(sprintf('Unknown %s name route', $name));
        }
        /*** @var RouteBase $route */
        $route = $this->routes[$name];
        if ($route->hasAttributes() === true && $parameters === []) {
            throw new InvalidArgumentException(sprintf('%s route need parameters: %s', $name, implode(',', $route->getVarsNames())));
        }

        $url = self::resolveUri($route, $parameters);
        if ($absoluteUrl === true) {
            $url = ltrim(Helper::trimPath($this->prefix), '/') . $url;
        }
        return $url;
    }

    private static function resolveUri(RouteBase $route, array $parameters): string
    {
        $uri = $route->getPath();
        foreach ($route->getVarsNames() as $variable) {
            $varName = trim($variable, '{\}');
            if (array_key_exists($varName, $parameters) === false) {
                throw new InvalidArgumentException(sprintf('%s not found in parameters to generate url', $varName));
            }
            $uri = str_replace($variable, (string)$parameters[$varName], $uri);
        }
        return $uri;
    }
}