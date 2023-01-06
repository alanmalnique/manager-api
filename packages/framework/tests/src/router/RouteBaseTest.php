<?php

namespace Test\Aeatech\Router;

use PHPUnit\Framework\TestCase;
use Aeatech\Router\RouteBase;

class RouteBaseTest extends TestCase {

    public function test_NotMatchRoute()
    {
        $routeWithoutAttribute = new RouteBase('view_articles','/view/article/', 'App\\Controller\\HomeController@home');
        $routeWithAttribute = new RouteBase('view_article','/view/article/{article}', 'App\\Controller\\HomeController@home');

        $this->assertFalse($routeWithoutAttribute->match('/view/article/1', 'GET'));
        $this->assertFalse($routeWithoutAttribute->match('/view/article/1', 'PUT'));
        $this->assertFalse($routeWithAttribute->match('/view/article/', 'POST'));
    }

    public function test_MatchRoute()
    {
        $routeWithAttribute = new RouteBase('view_article','/view/article/{article}', 'App\\Controller\\HomeController@home');
        $routeWithAttributes = new RouteBase('view_article_page','/view/article/{article}/{page}', 'App\\Controller\\HomeController@home');
        $routeWithoutAttribute = new RouteBase('view_articles','/view/article', 'App\\Controller\\HomeController@home');

        $this->assertTrue($routeWithAttribute->match('/view/article/1'));
        $this->assertTrue($routeWithAttributes->match('/view/article/1/24'));
        $this->assertTrue($routeWithoutAttribute->match('/view/article/'));
    }

    public function test_Exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new RouteBase('view_articles','/view', 'App\\Controller\\HomeController@home'))->methods([]);
    }
}
