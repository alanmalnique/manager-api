<?php

declare(strict_types=1);

namespace Aeatech\Router;

use Aeatech\Commons\Helper;
use Aeatech\Router\Provider\RouterProvider;
use Aeatech\Router\Traits\RouteTrait;
use InvalidArgumentException;
use function array_filter;
use function is_string;
use function preg_match;
use function preg_match_all;
use function reset;
use function str_replace;
use function trim;

/**
 * Class RouteBase
 * @package DevCoder
 */
final class RouteBase
{
    use RouteTrait;

    private string $name;
    private string $path;
    private string $prefix;

    /**
     * @var mixed
     */
    private mixed $handler;

    /**
     * @var array<string>
     */
    private array $methods = ['GET'];

    /**
     * @var array<string>
     */
    private array $attributes = [];

    /**
     * @var mixed
     */
    private mixed $middlewares = [];


    /**
     * RouteBase constructor.
     * @param string $name
     * @param string $path
     * @param mixed $handler
     *    $handler = [
     *      0 => (string) Controller name : HomeController::class.
     *      1 => (string|null) Method name or null if invoke method
     *    ]
     * @param array $methods
     */
    public function __construct(string $name, string $path, string $handler, array $middlewares = [])
    {
        $this->name = $name;
        $this->path = Helper::trimPath($path);
        $this->handler = $handler;
        $this->middlewares = $middlewares;
        $this->prefix = RouterProvider::getPrefix();
    }

    public function methods(array $methods = ['GET']): self
    {
        if (count($methods) > 0) {
            $this->methods = $methods;
            return $this;
        }
        throw new InvalidArgumentException('Empty HTTP Method.');
    }

    public function match(string $path): bool
    {
        $path = str_replace($this->prefix, "", $path);
        $regex = $this->getPath();
        foreach ($this->getVarsNames() as $variable) {
            $varName = trim($variable, '{\}');
            $regex = str_replace($variable, '(?P<' . $varName . '>[^/]++)', $regex);
        }

        if (preg_match('#^' . $regex . '$#sD', Helper::trimPath($path), $matches)) {
            $values = array_filter($matches, static function ($key) {
                return is_string($key);
            }, ARRAY_FILTER_USE_KEY);
            foreach ($values as $key => $value) {
                $this->attributes[$key] = $value;
            }
            return true;
        }
        return false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler(): mixed
    {
        return $this->handler;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getMiddlewares(): mixed
    {
        return $this->middlewares;
    }

    public function getVarsNames(): array
    {
        preg_match_all('/{[^}]*}/', $this->path, $matches);
        return reset($matches) ?? [];
    }

    public function hasAttributes(): bool
    {
        return $this->getVarsNames() !== [];
    }

    /**
     * @return array<string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}