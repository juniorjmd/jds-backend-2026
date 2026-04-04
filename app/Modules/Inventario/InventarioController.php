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

    public function __construct(
        Request $request,
        InventarioService $service
    ) {
        $this->request = $request;
        $this->service = $service;
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

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('RECORD_STOCK_MOVE_ERROR', $e->getMessage());
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

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('RECORD_STOCK_MOVE_DEVOLUCION_ERROR', $e->getMessage());
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

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('CANCEL_PRECHART_ERROR', $e->getMessage());
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

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('SAVE_PRECHART_ERROR', $e->getMessage());
        }
    }
}
