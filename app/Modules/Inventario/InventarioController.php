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

            $this->sendLegacyResponse($result);
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
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

            $this->sendLegacyResponse($result);
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
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
                ingressId: (int) $this->request->input('id_ingreso', 0),
                warehouseId: (int) $this->request->input('bodega_ingreso', 0)
            );

            $this->sendLegacyResponse($result);
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
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
                ingressId: (int) $this->request->input('id_ingreso', 0),
                ingressPayload: $this->normalizeIngreso($this->request->input('ingreso', []))
            );

            $this->sendLegacyResponse($result);
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function createDiscountActivity(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->createDiscountActivity(
                    $this->request->input('datosInsert', [])
                )
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function createProduct(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->createProduct(
                    $this->normalizeProducto($this->request->input('producto_enviado', []))
                )
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function updateProduct(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->updateProduct(
                    $this->normalizeProducto($this->request->input('producto_enviado', []))
                )
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function getAllProducts(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->getAllProducts($this->normalizeLimit($this->request->input('limit', [])))
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function getProductsByName(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->getProductsByName(
                    searchText: (string) $this->request->input('dato_busqueda', ''),
                    limit: $this->normalizeLimit($this->request->input('limit', []))
                )
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function getProductById(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->getProductById((string) $this->request->input('id_producto', ''))
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function getProductExistenceByDocument(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->getProductExistenceByDocument(
                    productId: (string) $this->request->input('id_producto', ''),
                    documentOrder: (int) $this->request->input('orden_documento', 0)
                )
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function getProductByIdOrBarcode(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->getProductByIdOrBarcode((string) $this->request->input('id_producto', ''))
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    public function returnProductSale(): void
    {
        try {
            $this->sendLegacyResponse(
                $this->service->returnProductSale($this->request->input('producto_enviado', []))
            );
        } catch (\Exception $e) {
            $this->sendLegacyError($e->getMessage());
        }
    }

    private function normalizeIngreso(mixed $ingreso): array
    {
        return is_array($ingreso) ? $ingreso : [];
    }

    private function normalizeProducto(mixed $producto): array
    {
        return is_array($producto) ? $producto : [];
    }

    private function normalizeLimit(mixed $limit): array
    {
        return is_array($limit) ? $limit : [];
    }

    private function sendLegacyResponse(array $payload): void
    {
        (new Response())
            ->status(200)
            ->json($payload)
            ->send();
    }

    private function sendLegacyError(string $message): void
    {
        (new Response())
            ->status(500)
            ->json(['error' => $message])
            ->send();
    }
}
