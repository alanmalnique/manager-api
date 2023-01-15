<?php

declare(strict_types=1);

namespace App\Model;

use Aeatech\Database\Model\DatabaseModel;

class User extends DatabaseModel
{
    protected string $table = 'user';
}