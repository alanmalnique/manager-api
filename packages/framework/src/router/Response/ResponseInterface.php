<?php

declare(strict_types=1);

namespace Aeatech\Router\Response;

interface ResponseInterface
{
    public static function json(array $body, int $statusCode): Response;
}
