<?php

namespace Aeatech\Database\Repository;

use Aeatech\Database\Interface\Database;
use Aeatech\Database\Model\DatabaseModel;

abstract class DatabaseBaseRepository
{
    private DatabaseModel $model;

    public function __construct()
    {
        $this->query();
    }

    protected function setInstanceModel($model)
    {
        $this->model = $model;
    }

    protected function hasFilter(array $filters, string $name): bool
    {
        return array_key_exists($name, $filters) && $name && $filters[$name] !== null;
    }

    protected function filter(array $filters, string $name, callable $callback): void
    {
        if ($this->hasFilter($filters, $name)) {
            $callback($filters[$name]);
        }
    }

    public function find(mixed $value, string $attribute="id", $columns = ['*']): array
    {
        return $this->query()
            ->select($columns)
            ->where($attribute, "=", $value)
            ->first();
    }

    public function findBy($attribute, $value, $columns = array('*')): array
    {
        return $this->query()
            ->select($columns)
            ->where($attribute, '=', $value)
            ->first();
    }

    public function all($columns = ['*']): array
    {
        return $this->query()
            ->select($columns)
            ->get();
    }

    public function create(array $data): bool|int
    {
        return $this->query()
            ->insert($data);
    }

    public function update(array $data, array $where): bool|int
    {
        return $this->query()
            ->update($data, $where);
    }

    public function delete($where): bool
    {
        return $this->query()
            ->delete($where);
    }

    public function query(): Database
    {
        return $this->model->query();
    }
}