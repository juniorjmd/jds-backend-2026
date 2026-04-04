<?php
declare(strict_types=1);

namespace App\Modules\Admin;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Admin\Services\AdminService;

class AdminController
{
    private Request $request;
    private AdminService $service;

    public function __construct(
        Request $request,
        AdminService $service
    ) {
        $this->request = $request;
        $this->service = $service;
    }

    /**
     * Obtiene lista de usuarios del sistema
     * Parámetros: _estado (opcional)
     */
    public function getUsers(): void
    {
        try {
            $estado = $this->request->input('estado', 'A');
            $result = $this->service->getUsers($estado);

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('GET_USERS_ERROR', $e->getMessage());
        }
    }

    /**
     * Crea un nuevo usuario
     * Parámetros: _login, _nombre1, _apellido1, _mail, _id_perfil (opcional)
     */
    public function createUser(): void
    {
        try {
            $userData = [
                'login' => $this->request->input('login', ''),
                'nombre1' => $this->request->input('nombre1', ''),
                'apellido1' => $this->request->input('apellido1', ''),
                'mail' => $this->request->input('mail', ''),
                'id_perfil' => (int) $this->request->input('id_perfil', 1)
            ];

            $result = $this->service->createUser($userData);

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('CREATE_USER_ERROR', $e->getMessage());
        }
    }

    /**
     * Actualiza información de usuario
     * Parámetros: _id, _estado, _id_perfil (opcional)
     */
    public function updateUser(): void
    {
        try {
            $userId = (int) $this->request->input('id', 0);
            $updateData = [
                'estado' => $this->request->input('estado', ''),
                'id_perfil' => (int) $this->request->input('id_perfil', 0)
            ];

            $result = $this->service->updateUser($userId, $updateData);

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('UPDATE_USER_ERROR', $e->getMessage());
        }
    }

    /**
     * Obtiene estructura de menús del sistema
     * Parámetros: _id_perfil (opcional)
     */
    public function getMenus(): void
    {
        try {
            $perfilId = (int) $this->request->input('id_perfil', 0);
            $result = $this->service->getMenus($perfilId);

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('GET_MENUS_ERROR', $e->getMessage());
        }
    }
}