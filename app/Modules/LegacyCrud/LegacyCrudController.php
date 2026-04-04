<?php
declare(strict_types=1);

namespace App\Modules\LegacyCrud;

use App\Core\Http\Response;

final class LegacyCrudController
{
    public function __construct(
        private LegacyCrudService $service
    ) {}

    public function select(): void
    {
        try {
            Response::ok($this->service->select());
        } catch (\Throwable $e) {
            Response::fail('DATABASE_GENERIC_CONTRUCT_SELECT_ERROR', $e->getMessage(), 400);
        }
    }

    public function selectByLoggedUser(): void
    {
        try {
            Response::ok($this->service->selectByLoggedUser());
        } catch (\Throwable $e) {
            Response::fail('DATABASE_GENERIC_CONTRUCT_SELECT_BY_USER_LOGGED_ERROR', $e->getMessage(), 400);
        }
    }

    public function selectMany(): void
    {
        try {
            Response::ok($this->service->selectMany());
        } catch (\Throwable $e) {
            Response::fail('DATABASE_GENERIC_CONTRUCT_SELECTS_ERROR', $e->getMessage(), 400);
        }
    }

    public function insert(): void
    {
        try {
            Response::ok($this->service->insert());
        } catch (\Throwable $e) {
            Response::fail('DATABASE_GENERIC_CONTRUCT_INSERT_ERROR', $e->getMessage(), 400);
        }
    }

    public function update(): void
    {
        try {
            Response::ok($this->service->update());
        } catch (\Throwable $e) {
            Response::fail('DATABASE_GENERIC_CONTRUCT_UPDATE_ERROR', $e->getMessage(), 400);
        }
    }

    public function delete(): void
    {
        try {
            Response::ok($this->service->delete());
        } catch (\Throwable $e) {
            Response::fail('DATABASE_GENERIC_CONTRUCT_DELETE_ERROR', $e->getMessage(), 400);
        }
    }

    public function procedure(): void
    {
        try {
            Response::ok($this->service->procedure());
        } catch (\Throwable $e) {
            Response::fail('DATABASE_GENERIC_CONTRUCT_PROCEDURE_ERROR', $e->getMessage(), 400);
        }
    }
}
