<?php
declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Core\Http\Request;

class AdminService
{
    private Request $request;
    private $authContext;

    public function __construct(Request $request, $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    /**
     * Obtiene lista de usuarios filtrados por estado
     */
    public function getUsers(string $estado = 'A'): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        $usuario = $authResult['compact_user'];

        // TODO: Verificar permisos administrativos
        // if (!$this->hasAdminPermissions($usuario)) {
        //     throw new \Exception('Permisos insuficientes');
        // }

        // TODO: Implementar con BD cuando esté disponible
        // Simulación de datos para desarrollo
        return [
            [
                'ID' => 1,
                'Login' => 'admin',
                'Nombre' => 'Administrador del Sistema',
                'estado' => 'A',
                'mail' => 'admin@jds.com'
            ],
            [
                'ID' => 2,
                'Login' => 'user1',
                'Nombre' => 'Usuario Ejemplo',
                'estado' => $estado,
                'mail' => 'user1@jds.com'
            ]
        ];
    }

    /**
     * Crea un nuevo usuario en el sistema
     */
    public function createUser(array $userData): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        // Validar campos requeridos
        if (empty($userData['login']) || empty($userData['nombre1']) || empty($userData['apellido1'])) {
            throw new \Exception('Campos requeridos faltantes: login, nombre1, apellido1');
        }

        if (empty($userData['mail']) || !filter_var($userData['mail'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Email inválido');
        }

        // TODO: Implementar con BD cuando esté disponible
        // Simulación de creación
        $newUser = [
            'ID' => rand(1000, 9999), // Simular ID generado
            'Login' => $userData['login'],
            'Nombre' => $userData['nombre1'] . ' ' . $userData['apellido1'],
            'mail' => $userData['mail'],
            'estado' => 'A',
            'id_perfil' => $userData['id_perfil'],
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];

        return $newUser;
    }

    /**
     * Actualiza información de un usuario existente
     */
    public function updateUser(int $userId, array $updateData): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        if ($userId <= 0) {
            throw new \Exception('ID de usuario inválido');
        }

        // TODO: Implementar con BD cuando esté disponible
        // Simulación de actualización
        $updatedUser = [
            'ID' => $userId,
            'estado' => $updateData['estado'] ?: 'A',
            'id_perfil' => $updateData['id_perfil'] ?: 1,
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];

        return $updatedUser;
    }

    /**
     * Obtiene estructura de menús del sistema
     */
    public function getMenus(int $perfilId = 0): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar con BD cuando esté disponible
        // Simulación de estructura de menús
        return [
            [
                'idmenus' => 1,
                'Nombre' => 'Dashboard',
                'Descripcion' => 'Panel principal',
                'PadreId' => null,
                'Icono' => 'dashboard',
                'Url' => '/dashboard',
                'Orden' => 1
            ],
            [
                'idmenus' => 2,
                'Nombre' => 'Usuarios',
                'Descripcion' => 'Gestión de usuarios',
                'PadreId' => null,
                'Icono' => 'users',
                'Url' => '/admin/users',
                'Orden' => 2
            ],
            [
                'idmenus' => 3,
                'Nombre' => 'Carwash',
                'Descripcion' => 'Módulo de lavado',
                'PadreId' => null,
                'Icono' => 'car',
                'Url' => '/carwash',
                'Orden' => 3
            ]
        ];
    }
}
