<?php
declare(strict_types=1);

namespace App\Modules\Carwash;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Carwash\Services\CarwashService;

class CarwashController
{
    private Request $request;
    private CarwashService $service;
    private Response $response;

    public function __construct(
        Request $request,
        CarwashService $service,
        Response $response
    ) {
        $this->request = $request;
        $this->service = $service;
        $this->response = $response;
    }

    /**
     * Abre una caja para transacciones
     * Soporta parámetros legacy: caja_motivo, caja_monto_inicial
     */
    public function openBox(): void
    {
        try {
            $result = $this->service->openBox(
                motivo: $this->request->input('caja_motivo', ''),
                initialAmount: (float) $this->request->input('caja_monto_inicial', 0)
            );

            $this->response
                ->status(200)
                ->json(['success' => true, 'data' => $result])
                ->send();
        } catch (\Exception $e) {
            $this->response
                ->status(400)
                ->json(['success' => false, 'error' => $e->getMessage()])
                ->send();
        }
    }

    /**
     * Cierra la caja activa
     */
    public function closeBox(): void
    {
        try {
            $result = $this->service->closeBox();

            $this->response
                ->status(200)
                ->json(['success' => true, 'data' => $result])
                ->send();
        } catch (\Exception $e) {
            $this->response
                ->status(400)
                ->json(['success' => false, 'error' => $e->getMessage()])
                ->send();
        }
    }

    /**
     * Cierra parcialmente la caja
     */
    public function closePartialBox(): void
    {
        try {
            $result = $this->service->closePartialBox();

            $this->response
                ->status(200)
                ->json(['success' => true, 'data' => $result])
                ->send();
        } catch (\Exception $e) {
            $this->response
                ->status(400)
                ->json(['success' => false, 'error' => $e->getMessage()])
                ->send();
        }
    }

    /**
     * Obtiene resumen de la caja
     */
    public function getBoxSummary(): void
    {
        try {
            $result = $this->service->getBoxSummary();

            $this->response
                ->status(200)
                ->json(['success' => true, 'data' => $result])
                ->send();
        } catch (\Exception $e) {
            $this->response
                ->status(400)
                ->json(['success' => false, 'error' => $e->getMessage()])
                ->send();
        }
    }
}
