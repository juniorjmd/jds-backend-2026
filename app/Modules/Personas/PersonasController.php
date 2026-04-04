<?php
declare(strict_types=1);

namespace App\Modules\Personas;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Personas\Services\PersonasService;

class PersonasController
{
    public function __construct(
        private Request $request,
        private PersonasService $service
    ) {
    }

    public function searchOdooPersonTitle(): void
    {
        try {
            $result = $this->service->searchOdooPersonTitle();
            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('SEARCH_ODOO_PERSON_TITLE_ERROR', $e->getMessage());
        }
    }

    public function getClientMasters(): void
    {
        try {
            $result = $this->service->getClientMasters();
            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('GET_CLIENT_MASTERS_ERROR', $e->getMessage());
        }
    }
}
