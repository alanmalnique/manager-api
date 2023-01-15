<?php

declare(strict_types=1);

namespace Aeatech\Database\Drivers\MySQL;

use Aeatech\Database\Drivers\Exception\ConnectionException;
use Aeatech\Database\Drivers\MySQL\Parser\MySQLQueryParser;
use Aeatech\Database\Interface\Database;
use Aeatech\Database\Service\DatabaseConfigService;
use PDO;

final class MySQLDriver implements Database
{
    private ?PDO $connection;
    private MySQLQueryParser $parser;
    private bool $connected = false;
    private array $config;
    protected array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public function __construct()
    {
        $this->config = (new DatabaseConfigService())->resolve()['connections']['mysql'];
    }

    /** @throws ConnectionException  */
    public function init(): self
    {
        if (!$this->connected) {
            $this->connect();
        }
        $this->parser = (new MySQLQueryParser($this->connection));
        return $this;
    }

    public function begin(): void
    {
        $this->connection->beginTransaction();
    }

    public function rollback(): void
    {
        $this->connection->rollBack();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function table(string $table): Database
    {
        $this->parser->table($table);
        return $this;
    }

    public function select(array|string $fields): Database
    {
        $this->parser->select($fields);
        return $this;
    }

    public function join(string $table, string $foreignKey, string $operator, string $localKey): Database
    {
        $this->parser->join($table, $foreignKey, $operator, $localKey);
        return $this;
    }

    public function innerJoin(string $table, string $foreignKey, string $operator, string $localKey): Database
    {
        $this->parser->innerJoin($table, $foreignKey, $operator, $localKey);
        return $this;
    }

    public function leftJoin(string $table, string $foreignKey, string $operator, string $localKey): Database
    {
        $this->parser->leftJoin($table, $foreignKey, $operator, $localKey);
        return $this;
    }

    public function rightJoin(string $table, string $foreignKey, string $operator, string $localKey): Database
    {
        $this->parser->rightJoin($table, $foreignKey, $operator, $localKey);
        return $this;
    }

    public function where(string $field, string $comparator, string $value): Database
    {
        $this->parser->where($field, $comparator, $value);
        return $this;
    }

    public function orWhere(string $field, string $comparator, string $value): Database
    {
        $this->parser->orWhere($field, $comparator, $value);
        return $this;
    }

    public function whereNull(string $field): Database
    {
        $this->parser->whereNull($field);
        return $this;
    }

    public function whereNotNull(string $field): Database
    {
        $this->parser->whereNotNull($field);
        return $this;
    }

    public function in(string $field, array $keys): Database
    {
        $this->parser->in($field, $keys);
        return $this;
    }

    public function notIn(string $field, array $keys): Database
    {
        $this->parser->notIn($field, $keys);
        return $this;
    }

    public function orIn(string $field, array $keys): Database
    {
        $this->parser->orIn($field, $keys);
        return $this;
    }

    public function orNotIn(string $field, array $keys): Database
    {
        $this->parser->orNotIn($field, $keys);
        return $this;
    }

    public function between(string $field, string $value1, string $value2): Database
    {
        $this->parser->between($field, $value1, $value2);
        return $this;
    }

    public function notBetween(string $field, string $value1, string $value2): Database
    {
        $this->parser->notBetween($field, $value1, $value2);
        return $this;
    }

    public function orBetween(string $field, string $value1, string $value2): Database
    {
        $this->parser->orBetween($field, $value1, $value2);
        return $this;
    }

    public function orNotBetween(string $field, string $value1, string $value2): Database
    {
        $this->parser->orNotBetween($field, $value1, $value2);
        return $this;
    }

    public function like(string $field, string $data): Database
    {
        $this->parser->like($field, $data);
        return $this;
    }

    public function orLike(string $field, string $data): Database
    {
        $this->parser->orLike($field, $data);
        return $this;
    }

    public function notLike(string $field, string $data): Database
    {
        $this->parser->notLike($field, $data);
        return $this;
    }

    public function orNotLike(string $field, string $data): Database
    {
        $this->parser->orNotLike($field, $data);
        return $this;
    }

    public function orderBy(string $field, string $orderType = 'ASC'): Database
    {
        $this->parser->orderBy($field, $orderType);
        return $this;
    }

    public function groupBy(array|string $field): Database
    {
        $this->parser->groupBy($field);
        return $this;
    }

    public function having(string $field, array|string $operator, string $value): Database
    {
        $this->having($field, $operator, $value);
        return $this;
    }

    public function limit(int $start, int $length): Database
    {
        $this->parser->limit($start, $length);
        return $this;
    }

    public function get(): ?array
    {
        return $this->parser->getAll();
    }

    public function paginate(int $perPage, int $page): array
    {
        $this->parser->pagination($perPage, $page);
        return $this->get();
    }

    public function first(): array
    {
        return $this->parser->get();
    }

    public function count(): int
    {
        return $this->parser->numRows();
    }

    public function insert(array $params): bool|int
    {
        return $this->parser->insert($params);
    }

    public function update(array $params, array $where = []): bool|int
    {
        foreach ($where as $conditions) {
            $this->parser->where($conditions[0], $conditions[1], $conditions[2]);
        }
        return $this->parser->update($params);
    }

    public function delete(array $where = []): bool
    {
        foreach ($where as $conditions) {
            $this->parser->where($conditions[0], $conditions[1], $conditions[2]);
        }
        return $this->parser->delete();
    }

    /** @throws ConnectionException */
    private function connect(): void
    {
        $dsn = $this->dsn();
        try {
            $this->connection = new PDO($dsn, $this->config['user'], $this->config['pass'], $this->options);
        } catch (\PDOException $exception) {
            throw new ConnectionException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    private function dsn(): string
    {
        $dsn = 'mysql:';
        $dsn .= 'host='.$this->config['host'].';';
        $dsn .= 'port='.$this->config['port'].';';
        $dsn .= 'dbname='.$this->config['data'].';';
        $dsn .= 'charset='.$this->config['charset'].';';
        return $dsn;
    }
}