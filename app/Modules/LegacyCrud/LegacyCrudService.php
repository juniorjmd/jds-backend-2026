<?php
declare(strict_types=1);

namespace App\Modules\LegacyCrud;

use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;
use RuntimeException;

final class LegacyCrudService
{
    public function __construct(
        private Request $request,
        private LegacyCrudRepository $repository,
        private AuthContext $authContext
    ) {}

    public function select(): array
    {
        $rows = $this->repository->select(
            table: $this->table(),
            columns: $this->columns(),
            where: $this->normalizeWhere($this->request->input('_where', [])),
            orderBy: $this->normalizeOrderBy($this->request->input('_orderBy', [])),
            limit: $this->normalizeLimit($this->request->input('_limit'))
        );

        return $this->formatRows($rows);
    }

    public function selectByLoggedUser(): array
    {
        $context = $this->requireUser();
        $column = (string) $this->request->input('_columnaUsuario', 'usuario');
        $where = $this->normalizeWhere($this->request->input('_where', []));
        $where[] = [
            'columna' => $column,
            'tipocomp' => '=',
            'dato' => (int) ($context['full_user']['ID'] ?? 0),
        ];

        $rows = $this->repository->select(
            table: $this->table(),
            columns: $this->columns(),
            where: $where,
            orderBy: $this->normalizeOrderBy($this->request->input('_orderBy', [])),
            limit: $this->normalizeLimit($this->request->input('_limit'))
        );

        return $this->formatRows($rows);
    }

    public function selectMany(): array
    {
        $tables = $this->request->input('_tablas', []);
        if (!is_array($tables) || $tables === []) {
            throw new RuntimeException('Debe enviar _tablas');
        }

        $results = [];
        foreach ($tables as $table) {
            $results[] = $this->repository->select((string) $table);
        }

        return [
            'records' => $results,
            'count' => count($results),
        ];
    }

    public function insert(): array
    {
        return $this->repository->insert($this->table(), $this->normalizeArrayData($this->request->input('_arraydatos', [])));
    }

    public function update(): array
    {
        return $this->repository->update(
            $this->table(),
            $this->normalizeArrayData($this->request->input('_arraydatos', [])),
            $this->normalizeWhere($this->request->input('_where', []))
        );
    }

    public function delete(): array
    {
        return $this->repository->delete(
            $this->table(),
            $this->normalizeWhere($this->request->input('_where', []))
        );
    }

    public function procedure(): array
    {
        $procedure = (string) $this->request->input('_procedure', '');
        if ($procedure === '') {
            throw new RuntimeException('Debe enviar _procedure');
        }

        $rows = $this->repository->executeProcedure(
            $procedure,
            array_values($this->normalizeArrayData($this->request->input('_arraydatos', [])))
        );

        return $this->formatRows($rows);
    }

    private function formatRows(array $rows): array
    {
        $objectColumns = $this->normalizeStringArray($this->request->input('_obj', []));

        if ($objectColumns !== []) {
            foreach ($rows as &$row) {
                foreach ($objectColumns as $column) {
                    if (!array_key_exists($column, $row) || !is_string($row[$column])) {
                        continue;
                    }

                    $decoded = json_decode($row[$column], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $row[$column] = $decoded;
                    }
                }
            }
            unset($row);
        }

        return [
            'records' => $rows,
            'count' => count($rows),
        ];
    }

    private function requireUser(): array
    {
        $context = $this->authContext->resolve($this->request);
        if (($context['success'] ?? false) !== true) {
            throw new RuntimeException((string) ($context['message'] ?? 'Usuario no autenticado'));
        }

        return $context;
    }

    private function table(): string
    {
        $table = (string) $this->request->input('_tabla', '');
        if ($table === '') {
            throw new RuntimeException('Debe enviar _tabla');
        }

        return $table;
    }

    private function columns(): array
    {
        $columns = $this->request->input('_columnas', ['*']);
        if (!is_array($columns) || $columns === []) {
            return ['*'];
        }

        return array_map(fn (mixed $column) => (string) $column, $columns);
    }

    private function normalizeWhere(mixed $where): array
    {
        return is_array($where) ? $where : [];
    }

    private function normalizeOrderBy(mixed $orderBy): array
    {
        return is_array($orderBy) ? $orderBy : [];
    }

    private function normalizeLimit(mixed $limit): ?int
    {
        if (is_array($limit)) {
            return null;
        }

        if ($limit === null || $limit === '') {
            return null;
        }

        $value = (int) $limit;
        return $value > 0 ? $value : null;
    }

    private function normalizeArrayData(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $context = null;
        $normalized = [];

        foreach ($payload as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $normalized[$key] = $this->normalizeValue($value, $context);
        }

        return $normalized;
    }

    private function normalizeValue(mixed $value, ?array &$context): mixed
    {
        if ($value === 'USUARIO_LOGUEADO') {
            $context ??= $this->requireUser();
            return (int) ($context['full_user']['ID'] ?? 0);
        }

        if (is_array($value) && count($value) === 2 && ($value[1] ?? null) === 'tabla') {
            return $value[0] ?? null;
        }

        if (is_array($value) && count($value) === 2) {
            return $value[0] ?? null;
        }

        return $value;
    }

    private function normalizeStringArray(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value) => is_string($value) ? $value : '',
            $payload
        )));
    }
}
