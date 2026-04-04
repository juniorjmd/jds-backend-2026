<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class DatosInicialesParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== DATOS INICIALES PARAMETERS TEST ==========\n\n";

        $this->testDatosInicialesActionsMapping();
        $this->testRoutesLoadsDatosInicialesActions();
        $this->testDatosInicialesControllerExists();
        $this->testActionDetected();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testDatosInicialesActionsMapping(): void
    {
        echo "TEST 1: DatosIniciales actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/datosiniciales-actions.php';

            if (!array_key_exists('GET_SUCURSAL_PRINCIPAL_DATA', $actions)) {
                throw new \Exception('Missing action GET_SUCURSAL_PRINCIPAL_DATA');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testRoutesLoadsDatosInicialesActions(): void
    {
        echo "TEST 2: Routes loads datosIniciales actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();

            if (!array_key_exists('GET_SUCURSAL_PRINCIPAL_DATA', $map)) {
                throw new \Exception('Action GET_SUCURSAL_PRINCIPAL_DATA not found in Routes::map()');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testDatosInicialesControllerExists(): void
    {
        echo "TEST 3: DatosInicialesController has required method... ";

        try {
            $controller = \App\Modules\DatosIniciales\DatosInicialesController::class;

            if (!method_exists($controller, 'getPrincipalBranchData')) {
                throw new \Exception('Method getPrincipalBranchData not found');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testActionDetected(): void
    {
        echo "TEST 4: Action is detected correctly... ";

        try {
            $request = new \App\Core\Http\Request('POST', [], ['action' => 'GET_SUCURSAL_PRINCIPAL_DATA'], [], []);

            if ($request->action() !== 'GET_SUCURSAL_PRINCIPAL_DATA') {
                throw new \Exception('action mismatch');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
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

(new DatosInicialesParametersTest())->run();
