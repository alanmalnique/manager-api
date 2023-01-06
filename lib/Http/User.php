<?php

declare(strict_types=1);

namespace App\Http;

use Aeatech\Router\Request\Request;
use Aeatech\Router\Response\Response;

final class User
{
    public function login(Request $request): Response
    {
        return Response::json(['test' => true]);
    }
}