<?php

declare(strict_types=1);

namespace Aeatech\Database\Interface;

interface Database
{
    public function begin(): void;
    public function rollback(): void;
    public function commit(): void;
    public function table(string $table): self;
    public function select(array|string $fields): self;
    public function join(string $table, string $foreignKey, string $operator, string $localKey): self;
    public function innerJoin(string $table, string $foreignKey, string $operator, string $localKey): self;
    public function leftJoin(string $table, string $foreignKey, string $operator, string $localKey): self;
    public function rightJoin(string $table, string $foreignKey, string $operator, string $localKey): self;
    public function where(string $field, string $comparator, string $value): self;
    public function orWhere(string $field, string $comparator, string $value): self;
    public function whereNull(string $field): self;
    public function whereNotNull(string $field): self;
    public function in(string $field, array $keys): self;
    public function notIn(string $field, array $keys): self;
    public function orIn(string $field, array $keys): self;
    public function orNotIn(string $field, array $keys): self;
    public function between(string $field, string $value1, string $value2): self;
    public function notBetween(string $field, string $value1, string $value2): self;
    public function orBetween(string $field, string $value1, string $value2): self;
    public function orNotBetween(string $field, string $value1, string $value2): self;
    public function like(string $field, string $data): self;
    public function orLike(string $field, string $data): self;
    public function notLike(string $field, string $data): self;
    public function orNotLike(string $field, string $data): self;
    public function orderBy(string $field, string $orderType): self;
    public function groupBy(array|string $field): self;
    public function having(string $field, array|string $operator, string $value): self;
    public function limit(int $start, int $length): self;
    public function get(): ?array;
    public function paginate(int $perPage, int $page): array;
    public function first(): array;
    public function count(): int;
    public function insert(array $params): bool|int;
    public function update(array $params, array $where): bool|int;
    public function delete(array $where): bool;
}