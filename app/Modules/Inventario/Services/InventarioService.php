<?php
declare(strict_types=1);

namespace App\Modules\Inventario\Services;

use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;

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
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar con BD cuando esté disponible
        return [
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
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar con BD cuando esté disponible
        return [
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
    public function cancelPrechart(int $ingressId): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar con BD cuando esté disponible
        return [
            'ingreso_id' => $ingressId,
            'estado' => 'CANCELADO',
            'fecha_cancelacion' => date('Y-m-d H:i:s'),
            'usuario' => $usuario['USUARIO'] ?? 'anonymous'
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
    public function savePrechart(array $items, int $ingressId): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar con BD cuando esté disponible
        return [
            'ingreso_id' => $ingressId,
            'items_guardados' => count($items),
            'estado' => 'GUARDADO',
            'fecha_guardado' => date('Y-m-d H:i:s'),
            'usuario' => $usuario['USUARIO'] ?? 'anonymous'
        ];
    }
}
