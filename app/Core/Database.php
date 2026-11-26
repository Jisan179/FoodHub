<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use Throwable;

/** Thin PDO wrapper. Every query goes through a prepared statement. */
final class Database
{
    private PDO $pdo;
    private int $depth = 0;

    public function __construct(array $config, bool $selectDatabase = true)
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $config['host'], $config['port']);
        if ($selectDatabase) {
            $dsn .= ';dbname=' . $config['name'];
        }
        $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        // Keep MySQL's clock in the same timezone as PHP
        $this->pdo->exec("SET time_zone = '" . date('P') . "'");
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function run(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $position = 0;
        foreach ($params as $key => $value) {
            $name = is_int($key) ? ++$position : $key;
            $type = is_int($value) ? PDO::PARAM_INT : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $statement->bindValue($name, is_bool($value) ? (int) $value : $value, is_bool($value) ? PDO::PARAM_INT : $type);
        }
        $statement->execute();

        return $statement;
    }

    public function all(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    public function one(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /** First column of the first row, or null. */
    public function value(string $sql, array $params = [])
    {
        $value = $this->run($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        );
        $this->run($sql, array_values($data));

        return (int) $this->pdo->lastInsertId();
    }

    /** Update by a single key column. Column names must come from code, never from user input. */
    public function update(string $table, array $data, string $keyColumn, int $key): int
    {
        $assignments = implode(', ', array_map(static fn ($column) => "$column = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $key;

        return $this->run("UPDATE $table SET $assignments WHERE $keyColumn = ?", $params)->rowCount();
    }

    /** Run a callback atomically. Nested calls join the outer transaction. */
    public function transaction(callable $callback)
    {
        if ($this->depth === 0) {
            $this->pdo->beginTransaction();
        }
        $this->depth++;
        try {
            $result = $callback($this);
            $this->depth--;
            if ($this->depth === 0) {
                $this->pdo->commit();
            }

            return $result;
        } catch (Throwable $error) {
            $this->depth--;
            if ($this->depth === 0 && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
}
