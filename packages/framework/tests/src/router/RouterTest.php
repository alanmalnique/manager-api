<?php

declare(strict_types=1);

namespace Test\DevCoder;

use Aeatech\Router\Exception\MethodNotAllowed;
use Aeatech\Router\Exception\RouteNotFound;
use Aeatech\Router\RouteBase;
use Aeatech\Router\Router;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $routes = [];
        $routes[] = new RouteBase('home_page', '/home', 'App\Controller\HomeController@home');
        $routes[] = new RouteBase('article_page', '/view/article', 'App\Controller\HomeController@article');
        $routes[] = new RouteBase('article_page_by_id', '/view/article/{id}', 'App\Controller\HomeController@article');
        $routes[] = new RouteBase('article_page_by_id_and_page', '/view/article/{id}/{page}', 'App\Controller\HomeController@article');

        $this->router = (new Router($routes, ''));
    }

    public function test_MatchRoute()
    {
        $route = $this->router->matchFromPath('/view/article/25', 'GET');
        $this->assertInstanceOf(RouteBase::class, $route);

        $this->assertNotEmpty($route->getHandler());
        $this->assertNotEmpty($route->getMethods());
        $this->assertSame(['id' => '25'], $route->getAttributes());
        $this->assertInstanceOf(RouteBase::class, $this->router->matchFromPath('/home', 'GET'));
    }

    public function test_MatchFromPath_ShouldThrowNotFoundException()
    {
        $this->expectException(RouteNotFound::class);
        $this->router->matchFromPath('/homes', 'GET');
    }

    public function test_MatchFromPath_ShouldThrowMethodNotAllowedException()
    {
        $this->expectException(MethodNotAllowed::class);
        $this->router->matchFromPath('/home', 'PUT');
    }

    public function test_GenerateUrl()
    {
        $urlHome = $this->router->generateUri('home_page');
        $urlArticle = $this->router->generateUri('article_page');
        $urlArticleWithParam = $this->router->generateUri('article_page_by_id', ['id' => 25]);
        $routeArticleWithParams = $this->router->generateUri('article_page_by_id_and_page', ['id' => 25, 'page' => 3]);

        $this->assertSame($urlHome, '/home');
        $this->assertSame($urlArticle, '/view/article');
        $this->assertSame($urlArticleWithParam, '/view/article/25');
        $this->assertSame($routeArticleWithParams, '/view/article/25/3');

        $this->expectException(InvalidArgumentException::class);
        $this->router->generateUri('article_page_by_id_and_page', ['id' => 25]);
    }

    public function test_GenerateAbsoluteUrl()
    {
        $urlHome = $this->router->generateUri('home_page', [], true);
        $urlArticle = $this->router->generateUri('article_page', [], true);
        $urlArticleWithParam = $this->router->generateUri('article_page_by_id', ['id' => 25], true);
        $routeArticleWithParams = $this->router->generateUri('article_page_by_id_and_page', ['id' => 25, 'page' => 3], true);

        $this->assertSame($urlHome, '/home');
        $this->assertSame($urlArticle, '/view/article');
        $this->assertSame($urlArticleWithParam, '/view/article/25');
        $this->assertSame($routeArticleWithParams, '/view/article/25/3');

        $this->expectException(InvalidArgumentException::class);
        $this->router->generateUri('article_page_by_id_and_page', ['id' => 25]);
    }
}
