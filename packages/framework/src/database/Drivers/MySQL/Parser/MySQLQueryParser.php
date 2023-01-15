<?php

namespace Aeatech\Database\Drivers\MySQL\Parser;

use Aeatech\Database\Drivers\Exception\ConnectionException;
use PDO;

class MySQLQueryParser
{
    private ?PDO $pdo = null;

    protected mixed $select = '*';
    protected mixed $from = null;
    protected mixed $where = null;
    protected mixed $limit = null;
    protected mixed $offset = null;
    protected mixed $join = null;
    protected mixed $orderBy = null;
    protected mixed $groupBy = null;
    protected mixed $having = null;
    protected mixed $grouped = false;
    protected mixed $numRows = 0;
    protected mixed $insertId = null;
    protected mixed $query = null;
    protected mixed $error = null;
    protected mixed $result = [];
    protected mixed $prefix = null;

    protected array $operators = ['=', '!=', '<', '>', '<=', '>=', '<>'];

    protected mixed $cache = null;
    protected mixed $cacheDir = null;

    protected int $queryCount = 0;

    protected bool $debug = true;

    protected int $transactionCount = 0;

    public function __construct(PDO $connection)
    {
        $this->pdo = $connection;
    }

    public function table(mixed $table): static
    {
        if (is_array($table)) {
            $from = '';
            foreach ($table as $key) {
                $from .= $this->prefix . $key . ', ';
            }
            $this->from = rtrim($from, ', ');
        } else {
            if (strpos($table, ',') > 0) {
                $tables = explode(',', $table);
                foreach ($tables as $key => &$value) {
                    $value = $this->prefix . ltrim($value);
                }
                $this->from = implode(', ', $tables);
            } else {
                $this->from = $this->prefix . $table;
            }
        }

        return $this;
    }

    public function select(array|string $fields): static
    {
        $select = is_array($fields) ? implode(', ', $fields) : $fields;
        $this->optimizeSelect($select);

        return $this;
    }

    public function count(string $field, ?string $name = null): static
    {
        $column = 'COUNT(' . $field . ')' . (!is_null($name) ? ' AS ' . $name : '');
        $this->optimizeSelect($column);

        return $this;
    }

    public function join(string $table, string $field1 = null, string $operator = null, string $field2 = null, string $type = ''): static
    {
        $on = $field1;
        $table = $this->prefix . $table;

        if (!is_null($operator)) {
            $on = !in_array($operator, $this->operators)
                ? $field1 . ' = ' . $operator . (!is_null($field2) ? ' ' . $field2 : '')
                : $field1 . ' ' . $operator . ' ' . $field2;
        }

        $this->join = (is_null($this->join))
            ? ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on
            : $this->join . ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;

        return $this;
    }

    public function innerJoin(string $table, string $field1, string $operator = '', string $field2 = ''): static
    {
        return $this->join($table, $field1, $operator, $field2, 'INNER ');
    }

    public function leftJoin(string $table, string $field1, string $operator = '', string $field2 = ''): static
    {
        return $this->join($table, $field1, $operator, $field2, 'LEFT ');
    }

    public function rightJoin(string $table, string $field1, string $operator = '', string $field2 = ''): static
    {
        return $this->join($table, $field1, $operator, $field2, 'RIGHT ');
    }

    public function where(array|string $where, string $operator = null, string $val = null, string $type = '', string $andOr = 'AND'): static
    {
        if (is_array($where) && !empty($where)) {
            $_where = [];
            foreach ($where as $column => $data) {
                $_where[] = $type . $column . '=' . $this->escape($data);
            }
            $where = implode(' ' . $andOr . ' ', $_where);
        } else {
            if (is_null($where) || empty($where)) {
                return $this;
            }

            if (is_array($operator)) {
                $params = explode('?', $where);
                $_where = '';
                foreach ($params as $key => $value) {
                    if (!empty($value)) {
                        $_where .= $type . $value . (isset($operator[$key]) ? $this->escape($operator[$key]) : '');
                    }
                }
                $where = $_where;
            } elseif (!in_array($operator, $this->operators) || $operator == false) {
                $where = $type . $where . ' = ' . $this->escape($operator);
            } else {
                $where = $type . $where . ' ' . $operator . ' ' . $this->escape($val);
            }
        }

        if ($this->grouped) {
            $where = '(' . $where;
            $this->grouped = false;
        }

        $this->where = is_null($this->where)
            ? $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    public function orWhere(array|string $where, string $operator = null, string $val = null): static
    {
        return $this->where($where, $operator, $val, '', 'OR');
    }

    public function whereNull(string $where, bool $not = false): static
    {
        $where = $where . ' IS ' . ($not ? 'NOT' : '') . ' NULL';
        $this->where = is_null($this->where) ? $where : $this->where . ' ' . 'AND ' . $where;

        return $this;
    }

    public function whereNotNull(string $where): static
    {
        return $this->whereNull($where, true);
    }

    public function grouped(\Closure $obj): static
    {
        $this->grouped = true;
        call_user_func_array($obj, [$this]);
        $this->where .= ')';

        return $this;
    }

    public function in(string $field, array $keys, string $type = '', string $andOr = 'AND'): static
    {
        if (is_array($keys)) {
            $_keys = [];
            foreach ($keys as $k => $v) {
                $_keys[] = is_numeric($v) ? $v : $this->escape($v);
            }
            $where = $field . ' ' . $type . 'IN (' . implode(', ', $_keys) . ')';

            if ($this->grouped) {
                $where = '(' . $where;
                $this->grouped = false;
            }

            $this->where = is_null($this->where)
                ? $where
                : $this->where . ' ' . $andOr . ' ' . $where;
        }

        return $this;
    }

    public function notIn(string $field, array $keys): static
    {
        return $this->in($field, $keys, 'NOT ', 'AND');
    }

    public function orIn(string $field, array $keys): static
    {
        return $this->in($field, $keys, '', 'OR');
    }

    public function orNotIn(string $field, array $keys): static
    {
        return $this->in($field, $keys, 'NOT ', 'OR');
    }

    public function between(string $field, int|string $value1, int|string $value2, string $type = '', string $andOr = 'AND'): static
    {
        $where = '(' . $field . ' ' . $type . 'BETWEEN ' . ($this->escape($value1) . ' AND ' . $this->escape($value2)) . ')';
        if ($this->grouped) {
            $where = '(' . $where;
            $this->grouped = false;
        }

        $this->where = is_null($this->where)
            ? $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    public function notBetween(string $field, int|string $value1, int|string $value2): static
    {
        return $this->between($field, $value1, $value2, 'NOT ', 'AND');
    }

    public function orBetween(string $field, int|string $value1, int|string $value2): static
    {
        return $this->between($field, $value1, $value2, '', 'OR');
    }

    public function orNotBetween(string $field, int|string $value1, int|string $value2): static
    {
        return $this->between($field, $value1, $value2, 'NOT ', 'OR');
    }

    public function like(string $field, string $data, string $type = '', string $andOr = 'AND'): static
    {
        $like = $this->escape($data);
        $where = $field . ' ' . $type . 'LIKE ' . $like;

        if ($this->grouped) {
            $where = '(' . $where;
            $this->grouped = false;
        }

        $this->where = is_null($this->where)
            ? $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    public function orLike(string $field, string $data): static
    {
        return $this->like($field, $data, '', 'OR');
    }

    public function notLike(string $field, string $data): static
    {
        return $this->like($field, $data, 'NOT ', 'AND');
    }

    public function orNotLike(string $field, string $data): static
    {
        return $this->like($field, $data, 'NOT ', 'OR');
    }

    public function limit(int $limit, int $limitEnd = null): static
    {
        $this->limit = !is_null($limitEnd)
            ? $limit . ', ' . $limitEnd
            : $limit;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function pagination(int $perPage, int $page): static
    {
        $this->limit = $perPage;
        $this->offset = (($page > 0 ? $page : 1) - 1) * $perPage;

        return $this;
    }

    public function orderBy(string $orderBy, string $orderDir = null): static
    {
        if (!is_null($orderDir)) {
            $this->orderBy = $orderBy . ' ' . strtoupper($orderDir);
        } else {
            $this->orderBy = stristr($orderBy, ' ') || strtolower($orderBy) === 'rand()'
                ? $orderBy
                : $orderBy . ' ASC';
        }

        return $this;
    }

    public function groupBy(array|string $groupBy): static
    {
        $this->groupBy = is_array($groupBy) ? implode(', ', $groupBy) : $groupBy;

        return $this;
    }

    public function having(string $field, array|string $operator = null, string $val = null): static
    {
        if (is_array($operator)) {
            $fields = explode('?', $field);
            $where = '';
            foreach ($fields as $key => $value) {
                if (!empty($value)) {
                    $where .= $value . (isset($operator[$key]) ? $this->escape($operator[$key]) : '');
                }
            }
            $this->having = $where;
        } elseif (!in_array($operator, $this->operators)) {
            $this->having = $field . ' > ' . $this->escape($operator);
        } else {
            $this->having = $field . ' ' . $operator . ' ' . $this->escape($val);
        }

        return $this;
    }

    public function numRows(): int
    {
        return $this->numRows;
    }

    public function insertId(): ?int
    {
        return $this->insertId;
    }

    /** @throws ConnectionException */
    public function error()
    {
        if ($this->debug === true) {
            if (php_sapi_name() === 'cli') {
                die("Query: " . $this->query . PHP_EOL . "Error: " . $this->error . PHP_EOL);
            }

            $msg = '<h1>Database Error</h1>';
            $msg .= '<h4>Query: <em style="font-weight:normal;">"' . $this->query . '"</em></h4>';
            $msg .= '<h4>Error: <em style="font-weight:normal;">' . $this->error . '</em></h4>';
            die($msg);
        }

        throw new ConnectionException($this->error . '. (' . $this->query . ')');
    }

    public function get(bool $type = null, string $argument = null): mixed
    {
        $this->limit = 1;
        $query = $this->getAll(true);
        return $type === true ? $query : $this->query($query, false, $type, $argument);
    }

    public function getAll(bool|string $type = null, string $argument = null): mixed
    {
        $query = 'SELECT ' . $this->select . ' FROM ' . $this->from;

        if (!is_null($this->join)) {
            $query .= $this->join;
        }

        if (!is_null($this->where)) {
            $query .= ' WHERE ' . $this->where;
        }

        if (!is_null($this->groupBy)) {
            $query .= ' GROUP BY ' . $this->groupBy;
        }

        if (!is_null($this->having)) {
            $query .= ' HAVING ' . $this->having;
        }

        if (!is_null($this->orderBy)) {
            $query .= ' ORDER BY ' . $this->orderBy;
        }

        if (!is_null($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;
        }

        if (!is_null($this->offset)) {
            $query .= ' OFFSET ' . $this->offset;
        }

        return $type === true ? $query : $this->query($query, true, $type, $argument);
    }

    public function insert(array $data, bool $type = false): mixed
    {
        $query = 'INSERT INTO ' . $this->from;

        $values = array_values($data);
        if (isset($values[0]) && is_array($values[0])) {
            $column = implode(', ', array_keys($values[0]));
            $query .= ' (' . $column . ') VALUES ';
            foreach ($values as $value) {
                $val = implode(', ', array_map([$this, 'escape'], $value));
                $query .= '(' . $val . '), ';
            }
            $query = trim($query, ', ');
        } else {
            $column = implode(', ', array_keys($data));
            $val = implode(', ', array_map([$this, 'escape'], $data));
            $query .= ' (' . $column . ') VALUES (' . $val . ')';
        }

        if ($type === true) {
            return $query;
        }

        if ($this->query($query, false)) {
            $this->insertId = $this->pdo->lastInsertId();
            return $this->insertId();
        }

        return false;
    }

    public function update(array $data, bool $type = false): mixed
    {
        $query = 'UPDATE ' . $this->from . ' SET ';
        $values = [];

        foreach ($data as $column => $val) {
            $values[] = $column . '=' . $this->escape($val);
        }
        $query .= implode(',', $values);

        if (!is_null($this->where)) {
            $query .= ' WHERE ' . $this->where;
        }

        if (!is_null($this->orderBy)) {
            $query .= ' ORDER BY ' . $this->orderBy;
        }

        if (!is_null($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;
        }

        return $type === true ? $query : $this->query($query, false);
    }

    public function delete(bool $type = false): mixed
    {
        $query = 'DELETE FROM ' . $this->from;

        if (!is_null($this->where)) {
            $query .= ' WHERE ' . $this->where;
        }

        if (!is_null($this->orderBy)) {
            $query .= ' ORDER BY ' . $this->orderBy;
        }

        if (!is_null($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;
        }

        if ($query === 'DELETE FROM ' . $this->from) {
            $query = 'TRUNCATE TABLE ' . $this->from;
        }

        return $type === true ? $query : $this->query($query, false);
    }

    public function exec(): mixed
    {
        if (is_null($this->query)) {
            return null;
        }

        $query = $this->pdo->exec($this->query);
        if ($query === false) {
            $this->error = $this->pdo->errorInfo()[2];
            $this->error();
        }

        return $query;
    }

    public function fetch(?string $type = null, ?string $argument = null, bool $all = false): mixed
    {
        if (is_null($this->query)) {
            return null;
        }

        $query = $this->pdo->query($this->query);
        if (!$query) {
            $this->error = $this->pdo->errorInfo()[2];
            $this->error();
        }

        $type = $this->getFetchType();
        $query->setFetchMode($type);

        $result = $all ? $query->fetchAll() : $query->fetch();
        $this->numRows = is_array($result) ? count($result) : 1;
        return $result;
    }

    public function fetchAll(?string $type = null, ?string $argument = null): mixed
    {
        return $this->fetch($type, $argument, true);
    }

    public function query(string $query, bool|array $all = true, string $type = null, string $argument = null): mixed
    {
        $this->reset();

        if (is_array($all) || func_num_args() === 1) {
            $params = explode('?', $query);
            $newQuery = '';
            foreach ($params as $key => $value) {
                if (!empty($value)) {
                    $newQuery .= $value . (isset($all[$key]) ? $this->escape($all[$key]) : '');
                }
            }
            $this->query = $newQuery;
            return $this;
        }

        $this->query = preg_replace('/\s\s+|\t\t+/', ' ', trim($query));
        $str = false;
        foreach (['select', 'optimize', 'check', 'repair', 'checksum', 'analyze'] as $value) {
            if (stripos($this->query, $value) === 0) {
                $str = true;
                break;
            }
        }

        $type = $this->getFetchType();
        $cache = false;
        if (!is_null($this->cache) && $type !== PDO::FETCH_CLASS) {
            $cache = $this->cache->getCache($this->query, $type === PDO::FETCH_ASSOC);
        }

        if (!$cache && $str) {
            $sql = $this->pdo->query($this->query);
            if ($sql) {
                $this->numRows = $sql->rowCount();
                if ($this->numRows > 0) {
                    if ($type === PDO::FETCH_CLASS) {
                        $sql->setFetchMode($type, $argument);
                    } else {
                        $sql->setFetchMode($type);
                    }
                    $this->result = $all ? $sql->fetchAll() : $sql->fetch();
                }

                if (!is_null($this->cache) && $type !== PDO::FETCH_CLASS) {
                    $this->cache->setCache($this->query, $this->result);
                }
                $this->cache = null;
            } else {
                $this->cache = null;
                $this->error = $this->pdo->errorInfo()[2];
                $this->error();
            }
        } elseif ((!$cache && !$str) || ($cache && !$str)) {
            $this->cache = null;
            $this->result = $this->pdo->exec($this->query);

            if ($this->result === false) {
                $this->error = $this->pdo->errorInfo()[2];
                $this->error();
            }
        } else {
            $this->cache = null;
            $this->result = $cache;
            $this->numRows = is_array($this->result) ? count($this->result) : ($this->result === '' ? 0 : 1);
        }

        $this->queryCount++;
        return $this->result;
    }

    public function escape(mixed $data): float|int|string
    {
        return $data === null ? 'NULL' : (
        is_int($data) || is_float($data) ? $data : $this->pdo->quote($data)
        );
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * @return void
     */
    protected function reset()
    {
        $this->select = '*';
        $this->from = null;
        $this->where = null;
        $this->limit = null;
        $this->offset = null;
        $this->orderBy = null;
        $this->groupBy = null;
        $this->having = null;
        $this->join = null;
        $this->grouped = false;
        $this->numRows = 0;
        $this->insertId = null;
        $this->query = null;
        $this->error = null;
        $this->result = [];
        $this->transactionCount = 0;
    }

    protected function getFetchType(): int
    {
        return PDO::FETCH_ASSOC;
    }

    private function optimizeSelect(string $fields): void
    {
        $this->select = $this->select === '*'
            ? $fields
            : $this->select . ', ' . $fields;
    }
}