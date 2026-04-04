<?php
declare(strict_types=1);

namespace App\Modules\Vehiculos;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Vehiculos\Services\VehiculosService;

class VehiculosController
{
    public function __construct(
        private Request $request,
        private VehiculosService $service
    ) {
    }

    public function createDocumentForVehicleService(): void
    {
        try {
            $payload = $this->request->input('arraydatos', []);

            if (!is_array($payload)) {
                $payload = [];
            }

            (new Response())
                ->status(200)
                ->json($this->service->createDocumentForVehicleService($payload))
                ->send();
        } catch (\Throwable $e) {
            (new Response())
                ->status(500)
                ->json(['error' => $e->getMessage()])
                ->send();
        }
    }
}
