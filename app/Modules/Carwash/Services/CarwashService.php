<?php
declare(strict_types=1);

namespace App\Modules\Carwash\Services;

use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;

class CarwashService
{
    private Request $request;
    private $authContext; // Flexible, puede ser AuthContext o cualquier objeto con método user()

    public function __construct(Request $request, $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    /**
     * Abre una caja para transacciones
     * 
     * @param string $motivo Razón de apertura (ej: "Apertura de jornada")
     * @param float $initialAmount Monto inicial en caja
     * @return array Estado de la caja
     * @throws \Exception Si hay error en la apertura
     */
    public function openBox(string $motivo = '', float $initialAmount = 0): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar lógica con BD cuando esté disponible
        // Por ahora retorna dato simulado para tests
        return [
            'caja_id' => 1,
            'usuario' => $usuario['USUARIO'] ?? 'anonymous',
            'estado' => 'ABIERTA',
            'fecha_hora_apertura' => date('Y-m-d H:i:s'),
            'monto_inicial' => $initialAmount,
            'motivo' => $motivo
        ];
    }

    /**
     * Cierra la caja activa
     * 
     * @return array Resumen de cierre
     * @throws \Exception Si hay error en el cierre
     */
    public function closeBox(): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar lógica con BD cuando esté disponible
        return [
            'caja_id' => 1,
            'usuario' => $usuario['USUARIO'] ?? 'anonymous',
            'estado' => 'CERRADA',
            'fecha_hora_cierre' => date('Y-m-d H:i:s'),
            'total_entrada' => 2500000,
            'total_salida' => 1200000,
            'saldo' => 1300000
        ];
    }

    /**
     * Cierra parcialmente la caja
     * 
     * @return array Estado de cierre parcial
     * @throws \Exception Si hay error
     */
    public function closePartialBox(): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        return [
            'caja_id' => 1,
            'usuario' => $usuario['USUARIO'] ?? 'anonymous',
            'estado' => 'PARCIALMENTE_CERRADA',
            'fecha_hora_cierre' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtiene resumen de la caja
     * 
     * @return array Resumen con movimientos
     * @throws \Exception Si hay error
     */
    public function getBoxSummary(): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Query a BD para obtener movimientos reales
        return [
            'caja_id' => 1,
            'usuario' => $usuario['USUARIO'] ?? 'anonymous',
            'estado' => 'ABIERTA',
            'total_entrada' => 2500000,
            'total_salida' => 1200000,
            'total_neto' => 1300000,
            'movimientos_count' => 15,
            'fecha_hora_apertura' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ];
    }
}
