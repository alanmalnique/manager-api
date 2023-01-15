<?php

declare(strict_types=1);

namespace Aeatech\Database\Repository;

use Aeatech\Database\Model\DatabaseModel;

abstract class DatabaseRepository extends DatabaseBaseRepository
{
    private mixed $fqcn;

    public function __construct()
    {
        $modelFQCN = str_replace('\\Repository\\', '\\Model\\', static::class);

        $this->fqcn =  preg_replace('@Repository$@', '', $modelFQCN);

        $this->setModel();

        parent::__construct();
    }

    public function setModel(): void
    {
        $model = new $this->fqcn;
        if (!$model instanceof DatabaseModel) {
            throw new \RuntimeException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        $this->setInstanceModel($model);
    }
}