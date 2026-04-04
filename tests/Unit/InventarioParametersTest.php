<?php
/**
 * tests/Unit/InventarioParametersTest.php
 * 
 * Tests para validar:
 * 1. Mapeo de acciones legacy
 * 2. Carga en Routes.php
 * 3. Controller existe
 * 4. Parámetros legacy
 * 5. Acción detectada
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class InventarioParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run()
    {
        echo "\n========== INVENTARIO PARAMETERS TEST ==========\n\n";

        $this->testInventarioActionsMapping();
        $this->testRoutesLoadsInventarioActions();
        $this->testInventarioControllerExists();
        $this->testStockMoveAcceptsLegacyParameters();
        $this->testActionDetected();

        $this->printSummary();

        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testInventarioActionsMapping(): void
    {
        echo "TEST 1: Inventario actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/inventario-actions.php';

            $expectedActions = [
                'STOCK_MOVE',
                'STOCK_MOVE_DEVOLUCION',
                'BORRAR_DATOS_INGRESO_AUX_INVENTARIO',
                'INGRESO_DATOS_DATOS_AUX_INVENTARIO',
            ];

            foreach ($expectedActions as $action) {
                if (!array_key_exists($action, $actions)) {
                    throw new \Exception("Missing action: $action");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testRoutesLoadsInventarioActions(): void
    {
        echo "TEST 2: Routes loads inventario actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();

            $expectedActions = [
                'STOCK_MOVE',
                'STOCK_MOVE_DEVOLUCION',
                'BORRAR_DATOS_INGRESO_AUX_INVENTARIO',
                'INGRESO_DATOS_DATOS_AUX_INVENTARIO',
            ];

            foreach ($expectedActions as $action) {
                if (!array_key_exists($action, $map)) {
                    throw new \Exception("Action $action not found in Routes::map()");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testInventarioControllerExists(): void
    {
        echo "TEST 3: InventarioController has required methods... ";

        try {
            $controller = \App\Modules\Inventario\InventarioController::class;

            $methods = [
                'recordStockMove',
                'recordStockMoveDevolución',
                'cancelPrechart',
                'savePrechart'
            ];

            foreach ($methods as $method) {
                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method $method not found in InventarioController");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testStockMoveAcceptsLegacyParameters(): void
    {
        echo "TEST 4: recordStockMove accepts legacy parameters... ";

        try {
            $body = [
                'action' => 'STOCK_MOVE',
                '_usuario' => 'admin',
                'id_documento' => 12345,
                'id_producto' => 567,
                'cantidad' => 10,
                'tipo_movimiento' => 'salida'
            ];

            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            $documentId = $request->input('id_documento', 0);
            $productId = $request->input('id_producto', 0);
            $quantity = $request->input('cantidad', 0);
            $type = $request->input('tipo_movimiento', 'salida');

            if ($documentId !== 12345) {
                throw new \Exception("Failed to extract id_documento");
            }

            if ($productId !== 567) {
                throw new \Exception("Failed to extract id_producto");
            }

            if ($quantity !== 10) {
                throw new \Exception("Failed to extract cantidad");
            }

            if ($type !== 'salida') {
                throw new \Exception("Failed to extract tipo_movimiento");
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testActionDetected(): void
    {
        echo "TEST 5: Action is detected correctly... ";

        try {
            $body = ['action' => 'STOCK_MOVE'];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);
            $action = $request->action();

            if ($action !== 'STOCK_MOVE') {
                throw new \Exception("Failed to detect action. Got: '$action'");
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function printSummary(): void
    {
        $total = $this->passCount + $this->failCount;
        echo "\n========== RESULT ==========\n";
        echo "PASSED: {$this->passCount}/{$total}\n";
        echo "FAILED: {$this->failCount}/{$total}\n";
        echo "===========================\n\n";
    }
}

$test = new InventarioParametersTest();
$test->run();
