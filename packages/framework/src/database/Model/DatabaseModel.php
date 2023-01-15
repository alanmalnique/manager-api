<?php

declare(strict_types=1);

namespace Aeatech\Database\Model;

use Aeatech\Database\Interface\Database;
use Aeatech\Database\Provider\DatabaseProvider;

abstract class DatabaseModel extends DatabaseProvider
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected string $keyType = 'int';
    protected array $fillable;
    protected array $hidden;
    protected array $relations;

    public function query(): Database
    {
        return self::database()->table($this->table);
    }
}