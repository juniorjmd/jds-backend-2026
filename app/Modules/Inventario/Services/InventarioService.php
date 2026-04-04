<?php
declare(strict_types=1);

namespace App\Modules\Inventario\Services;

use App\Core\Http\Request;

class InventarioService
{
    private Request $request;
    private $authContext; // Flexible, puede ser cualquier objeto con método user()

    public function __construct(Request $request, $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    /**
     * Registra un movimiento de stock (entrada/salida)
     * 
     * @param int $documentId ID del documento
     * @param int $productId ID del producto
     * @param float $quantity Cantidad movida
     * @param string $movementType Tipo de movimiento (salida, entrada, ajuste)
     * @return array Estado del movimiento
     * @throws \Exception
     */
    public function recordStockMove(
        int $documentId,
        int $productId,
        float $quantity,
        string $movementType = 'salida'
    ): array {
        $authResult = $this->authContext->resolve($this->request);
        
        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }
        
        $usuario = $authResult['compact_user'];

        // TODO: Implementar con BD cuando esté disponible
        return [
            'error' => 'ok',
            'stock_move_id' => 1,
            'documento_id' => $documentId,
            'producto_id' => $productId,
            'cantidad_movida' => $quantity,
            'tipo_movimiento' => $movementType,
            'cantidad_anterior' => 100,
            'cantidad_nueva' => $movementType === 'salida' ? 100 - $quantity : 100 + $quantity,
            'fecha_hora' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Registra una devolución de stock
     * 
     * @param int $documentId ID del documento
     * @param int $productId ID del producto
     * @param float $quantity Cantidad devuelta
     * @return array Estado después de devolución
     * @throws \Exception
     */
    public function recordStockMoveDevolución(
        int $documentId,
        int $productId,
        float $quantity
    ): array {
        $authResult = $this->authContext->resolve($this->request);
        
        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }
        
        $usuario = $authResult['compact_user'];

        // TODO: Implementar con BD cuando esté disponible
        return [
            'error' => 'ok',
            'stock_move_devolucion_id' => 1,
            'documento_id' => $documentId,
            'producto_id' => $productId,
            'cantidad_devuelta' => $quantity,
            'cantidad_anterior' => 40,
            'cantidad_nueva' => 40 + $quantity,
            'tipo_operacion' => 'devolucion',
            'fecha_hora' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Cancela una pre-carga de ingreso
     * 
     * @param int $ingressId ID de la pre-carga a cancelar
     * @return array Resultado de cancelación
     * @throws \Exception
     */
    public function cancelPrechart(int $ingressId, int $warehouseId = 0): array
    {
        $usuario = $this->resolveAuthenticatedUser();

        return [
            'error' => 'ok',
            'ingreso_id' => $ingressId,
            'bodega_ingreso' => $warehouseId,
            'numdata' => 0,
            'datos' => [],
            'estado' => 'CANCELADO',
            'fecha_cancelacion' => date('Y-m-d H:i:s'),
            'usuario' => $usuario['nombre'] ?? 'anonymous'
        ];
    }

    /**
     * Guarda pre-carga de ingreso
     * 
     * @param array $items Items a guardar
     * @param int $ingressId ID del ingreso
     * @return array Pre-carga guardada
     * @throws \Exception
     */
    public function savePrechart(array $items, int $ingressId, array $ingressPayload = []): array
    {
        $usuario = $this->resolveAuthenticatedUser();

        if ($ingressPayload !== []) {
            $items = [[
                'id' => 1,
                'idProducto' => $ingressPayload['idProducto'] ?? $ingressPayload['id_producto'] ?? null,
                'cantidad' => $ingressPayload['cantidad'] ?? 0,
                'bodega' => $ingressPayload['bodega']['id'] ?? null,
                'nombreBodega' => $ingressPayload['bodega']['nombre'] ?? '',
                'usuario' => $usuario['nombre'] ?? 'anonymous',
            ]];
        }

        return [
            'error' => 'ok',
            'ingreso_id' => $ingressId,
            'numdata' => count($items),
            'datos' => $items,
            'items_guardados' => count($items),
            'estado' => 'GUARDADO',
            'fecha_guardado' => date('Y-m-d H:i:s'),
            'usuario' => $usuario['nombre'] ?? 'anonymous'
        ];
    }

    public function createDiscountActivity(array $data): array
    {
        $this->resolveAuthenticatedUser();

        if (trim((string) ($data['nombre'] ?? '')) === '') {
            throw new \Exception('Debe enviar el nombre de la actividad');
        }

        return [
            'error' => 'ok',
            'actividad_id' => 1,
            'data' => $data,
        ];
    }

    public function createProduct(array $product): array
    {
        $this->resolveAuthenticatedUser();

        $this->validateProductPayload($product);

        return [
            'error' => 'ok',
            'producto' => $product,
            'numdata' => 1,
        ];
    }

    public function updateProduct(array $product): array
    {
        $this->resolveAuthenticatedUser();

        $this->validateProductPayload($product);

        return [
            'error' => 'ok',
            'producto' => $product,
            'numdata' => 1,
        ];
    }

    public function getAllProducts(array $limit = []): array
    {
        $this->resolveAuthenticatedUser();

        $products = $this->sampleProducts();
        $products = $this->applyLimit($products, $limit);

        return [
            'error' => 'ok',
            'numdata' => count($products),
            'productos' => $products,
            'data' => $products,
            'query' => 'sample_products',
        ];
    }

    public function getProductsByName(string $searchText, array $limit = []): array
    {
        $this->resolveAuthenticatedUser();

        $needle = mb_strtolower(trim($searchText));
        $products = array_values(array_filter(
            $this->sampleProducts(),
            static function (array $product) use ($needle): bool {
                if ($needle === '') {
                    return true;
                }

                return str_contains(mb_strtolower((string) ($product['nombre'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($product['barcode'] ?? '')), $needle);
            }
        ));

        $products = $this->applyLimit($products, $limit);

        return [
            'error' => 'ok',
            'numdata' => count($products),
            'productos' => $products,
            'data' => $products,
            'query' => 'sample_products_by_name',
        ];
    }

    public function getProductById(string $productId): array
    {
        $this->resolveAuthenticatedUser();

        $product = $this->findSampleProductByIdOrBarcode($productId);
        if ($product === null) {
            return [
                'error' => 'ok',
                'numdata' => 0,
                'producto' => [],
                'data' => [],
                'query' => 'sample_product_by_id',
            ];
        }

        return [
            'error' => 'ok',
            'numdata' => 1,
            'producto' => $product,
            'data' => [$product],
            'query' => 'sample_product_by_id',
        ];
    }

    public function getProductExistenceByDocument(string $productId, int $documentOrder): array
    {
        $this->resolveAuthenticatedUser();

        $product = $this->findSampleProductByIdOrBarcode($productId);
        $existence = $product['existencias'][0]['cant_actual'] ?? 0;
        $warehouseName = $product['existencias'][0]['nombreBodega'] ?? 'Bodega principal';
        $warehouseId = $product['existencias'][0]['id_bodega'] ?? 1;

        return [
            'error' => 'ok',
            'numdata' => $product === null ? 0 : 1,
            'data' => [
                'nombreBodega' => $warehouseName,
                'idProducto' => $productId,
                'existencia' => $existence,
                'idBodega' => $warehouseId,
                'ordenDocumento' => $documentOrder,
            ],
            'query' => 'sample_product_existence',
        ];
    }

    public function getProductByIdOrBarcode(string $productId): array
    {
        $this->resolveAuthenticatedUser();

        $product = $this->findSampleProductByIdOrBarcode($productId);
        $products = $product !== null ? [$product] : [];

        return [
            'error' => 'ok',
            'numdata' => count($products),
            'data' => $products,
            'query' => 'sample_product_by_id_or_barcode',
        ];
    }

    public function returnProductSale(mixed $product): array
    {
        $this->resolveAuthenticatedUser();

        $line = is_array($product) ? $product : [];

        return [
            'error' => 'ok',
            'producto' => $line,
            'estado' => 'DEVUELTO',
        ];
    }

    private function resolveAuthenticatedUser(): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        return $authResult['compact_user'] ?? [];
    }

    private function validateProductPayload(array $product): void
    {
        if (trim((string) ($product['nombre'] ?? '')) === '') {
            throw new \Exception('Debe enviar el nombre del producto');
        }
    }

    private function sampleProducts(): array
    {
        return [
            [
                'id' => 101,
                'nombre' => 'Shampoo Premium',
                'barcode' => '770101',
                'tipo_producto' => 1,
                'idCategoria' => 10,
                'idMarca' => 20,
                'porcent_iva' => 19,
                'precios' => [[
                    'id_producto' => 101,
                    'precio_con_iva' => 12000,
                    'precio_antes_de_iva' => 10084.03,
                    'valor_iva' => 1915.97,
                ]],
                'existencias' => [[
                    'id_bodega' => 1,
                    'nombreBodega' => 'Bodega principal',
                    'cant_actual' => 12,
                ]],
            ],
            [
                'id' => 202,
                'nombre' => 'Lavado Full',
                'barcode' => '770202',
                'tipo_producto' => 2,
                'idCategoria' => 11,
                'idMarca' => 21,
                'porcent_iva' => 0,
                'precios' => [[
                    'id_producto' => 202,
                    'precio_con_iva' => 25000,
                    'precio_antes_de_iva' => 25000,
                    'valor_iva' => 0,
                ]],
                'existencias' => [[
                    'id_bodega' => 1,
                    'nombreBodega' => 'Bodega principal',
                    'cant_actual' => 999,
                ]],
            ],
        ];
    }

    private function findSampleProductByIdOrBarcode(string $value): ?array
    {
        foreach ($this->sampleProducts() as $product) {
            if ((string) ($product['id'] ?? '') === trim($value) || (string) ($product['barcode'] ?? '') === trim($value)) {
                return $product;
            }
        }

        return null;
    }

    private function applyLimit(array $items, array $limit): array
    {
        if (count($limit) >= 2) {
            return array_slice($items, (int) $limit[0], (int) $limit[1]);
        }

        return $items;
    }
}
