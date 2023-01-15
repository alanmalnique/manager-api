<?php

declare(strict_types=1);

namespace App\Http;

use Aeatech\Router\Request\Request;
use Aeatech\Router\Response\Response;
use App\Repository\UserRepository;

final class User
{
    private UserRepository $userRepository;
    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function login(Request $request): Response
    {
        return Response::json($this->userRepository->all());
    }
}