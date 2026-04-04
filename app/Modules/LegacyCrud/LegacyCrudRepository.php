<?php
declare(strict_types=1);

namespace App\Modules\LegacyCrud;

use App\Core\Database\BaseRepository;
use PDO;
use RuntimeException;

final class LegacyCrudRepository extends BaseRepository
{
    public function select(
        string $table,
        array $columns = ['*'],
        array $where = [],
        array $orderBy = [],
        ?int $limit = null
    ): array {
        $quotedColumns = $this->quoteColumns($columns);
        $sql = 'SELECT ' . implode(', ', $quotedColumns) . ' FROM ' . $this->quoteIdentifier($table);
        [$whereSql, $params] = $this->buildWhereClause($where);

        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }

        if ($orderBy !== []) {
            $sql .= ' ORDER BY ' . $this->buildOrderBy($orderBy);
        }

        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $this->fetchAll($sql, $params);
    }

    public function insert(string $table, array $data): array
    {
        if ($data === []) {
            throw new RuntimeException('No se enviaron datos para insertar');
        }

        $columns = array_keys($data);
        $quotedColumns = array_map(fn (string $column) => $this->quoteIdentifier($column), $columns);
        $placeholders = array_map(fn (string $column) => ':' . $column, $columns);
        $params = [];

        foreach ($data as $column => $value) {
            $params[':' . $column] = $value;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->quoteIdentifier($table),
            implode(', ', $quotedColumns),
            implode(', ', $placeholders)
        );

        $this->execute($sql, $params);

        return [
            'affected' => 1,
            'insertId' => (int) $this->db->lastInsertId(),
        ];
    }

    public function update(string $table, array $data, array $where): array
    {
        if ($data === []) {
            throw new RuntimeException('No se enviaron datos para actualizar');
        }

        [$whereSql, $params] = $this->buildWhereClause($where, 'w_');
        if ($whereSql === '') {
            throw new RuntimeException('No se permite actualizar sin condiciones');
        }

        $setParts = [];
        foreach ($data as $column => $value) {
            $param = ':s_' . $column;
            $setParts[] = $this->quoteIdentifier($column) . ' = ' . $param;
            $params[$param] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->quoteIdentifier($table),
            implode(', ', $setParts),
            $whereSql
        );

        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return [
            'affected' => $stmt->rowCount(),
        ];
    }

    public function delete(string $table, array $where): array
    {
        [$whereSql, $params] = $this->buildWhereClause($where, 'w_');
        if ($whereSql === '') {
            throw new RuntimeException('No se permite eliminar sin condiciones');
        }

        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->quoteIdentifier($table),
            $whereSql
        );

        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return [
            'affected' => $stmt->rowCount(),
        ];
    }

    public function executeProcedure(string $procedure, array $params = []): array
    {
        $procedure = trim($procedure);
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $procedure)) {
            throw new RuntimeException("Procedimiento inválido: {$procedure}");
        }

        $placeholders = [];
        $bindings = [];
        $index = 0;

        foreach ($params as $value) {
            $key = ':p' . $index++;
            $placeholders[] = $key;
            $bindings[$key] = $value;
        }

        $sql = sprintf(
            'CALL %s(%s)',
            $procedure,
            implode(', ', $placeholders)
        );

        $stmt = $this->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $rows;
    }

    private function quoteColumns(array $columns): array
    {
        if ($columns === []) {
            return ['*'];
        }

        return array_map(function (string $column): string {
            if ($column === '*') {
                return '*';
            }

            return $this->quoteIdentifier($column);
        }, $columns);
    }

    private function buildOrderBy(array $orderBy): string
    {
        $parts = [];

        foreach ($orderBy as $index => $order) {
            if (!is_array($order) || count($order) < 1) {
                continue;
            }

            $column = (string) ($order[0] ?? '');
            $direction = strtoupper((string) ($order[1] ?? 'ASC'));
            if ($column === '') {
                continue;
            }

            if (!in_array($direction, ['ASC', 'DESC'], true)) {
                $direction = 'ASC';
            }

            $parts[] = $this->quoteIdentifier($column) . ' ' . $direction;
        }

        return implode(', ', $parts);
    }

    private function buildWhereClause(array $where, string $prefix = 'p_'): array
    {
        $parts = [];
        $params = [];

        foreach (array_values($where) as $index => $condition) {
            if (!is_array($condition)) {
                continue;
            }

            $column = trim((string) ($condition['columna'] ?? ''));
            $operator = strtoupper(trim((string) ($condition['tipocomp'] ?? '=')));
            $relation = strtoupper(trim((string) ($condition['relacion'] ?? 'AND')));
            $value = $condition['dato'] ?? null;

            if ($column === '') {
                continue;
            }

            $sqlRelation = $index === 0 ? '' : (($relation === 'OR') ? ' OR ' : ' AND ');
            $param = ':' . $prefix . $index;

            $comparison = match ($operator) {
                '=', '<>', '!=', '>', '>=', '<', '<=' => $this->quoteIdentifier($column) . " {$operator} {$param}",
                'LIKE' => $this->quoteIdentifier($column) . " LIKE {$param}",
                '=F' => $this->quoteIdentifier($column) . ' = ' . $this->quoteFunction((string) $value),
                'INS' => $this->quoteIdentifier($column) . ' IN ' . $this->buildSubquery($value),
                default => throw new RuntimeException("Operador no soportado: {$operator}"),
            };

            if (!in_array($operator, ['=F', 'INS'], true)) {
                $params[$param] = $operator === 'LIKE' ? '%' . $value . '%' : $value;
            }

            $parts[] = $sqlRelation . $comparison;
        }

        return [implode('', $parts), $params];
    }

    private function buildSubquery(mixed $value): string
    {
        if (!is_array($value)) {
            throw new RuntimeException('Subconsulta inválida');
        }

        $column = (string) ($value['columna'] ?? '');
        $table = (string) ($value['tabla'] ?? '');
        $checkColumn = (string) ($value['colValidacion'] ?? '');
        $checkValue = (string) ($value['datoValidacion'] ?? '');

        if ($column === '' || $table === '' || $checkColumn === '' || $checkValue === '') {
            throw new RuntimeException('Subconsulta incompleta');
        }

        return sprintf(
            '(SELECT %s FROM %s WHERE %s = %s)',
            $this->quoteIdentifier($column),
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($checkColumn),
            $checkValue
        );
    }

    private function quoteFunction(string $value): string
    {
        $value = trim($value);

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\((.*)\)$/', $value)) {
            throw new RuntimeException("Expresión de función inválida: {$value}");
        }

        return $value;
    }

    private function quoteIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            return '`' . $identifier . '`';
        }

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            $parts = explode('.', $identifier);
            return '`' . $parts[0] . '`.`' . $parts[1] . '`';
        }

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\([a-zA-Z_][a-zA-Z0-9_]*\)$/', $identifier)) {
            return $identifier;
        }

        throw new RuntimeException("Identificador inválido: {$identifier}");
    }
}
