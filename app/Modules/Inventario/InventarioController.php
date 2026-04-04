<?php
declare(strict_types=1);

namespace App\Modules\Inventario;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Inventario\Services\InventarioService;

class InventarioController
{
    private Request $request;
    private InventarioService $service;
    private Response $response;

    public function __construct(
        Request $request,
        InventarioService $service,
        Response $response
    ) {
        $this->request = $request;
        $this->service = $service;
        $this->response = $response;
    }

    /**
     * Registra un movimiento de stock
     * Parámetros: id_documento, id_producto, cantidad, tipo_movimiento
     */
    public function recordStockMove(): void
    {
        try {
            $result = $this->service->recordStockMove(
                documentId: (int) $this->request->input('id_documento', 0),
                productId: (int) $this->request->input('id_producto', 0),
                quantity: (float) $this->request->input('cantidad', 0),
                movementType: $this->request->input('tipo_movimiento', 'salida')
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
     * Registra una devolución de stock
     * Parámetros: id_documento, id_producto, cantidad
     */
    public function recordStockMoveDevolución(): void
    {
        try {
            $result = $this->service->recordStockMoveDevolución(
                documentId: (int) $this->request->input('id_documento', 0),
                productId: (int) $this->request->input('id_producto', 0),
                quantity: (float) $this->request->input('cantidad', 0)
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
     * Cancela una pre-carga de ingreso
     * Parámetros: id_ingreso
     */
    public function cancelPrechart(): void
    {
        try {
            $result = $this->service->cancelPrechart(
                ingressId: (int) $this->request->input('id_ingreso', 0)
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
     * Guarda una pre-carga de ingreso
     * Parámetros: listado (JSON), id_ingreso
     */
    public function savePrechart(): void
    {
        try {
            $listado = $this->request->input('listado', '[]');
            if (is_string($listado)) {
                $listado = json_decode($listado, true) ?: [];
            }

            $result = $this->service->savePrechart(
                items: $listado,
                ingressId: (int) $this->request->input('id_ingreso', 0)
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
}
