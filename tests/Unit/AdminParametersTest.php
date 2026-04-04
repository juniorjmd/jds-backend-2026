<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;

class AdminParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== ADMIN PARAMETERS TEST ==========\n\n";

        $this->testLegacyParameterParsing();
        $this->testParameterDefaults();
        $this->testActionParameter();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testLegacyParameterParsing(): void
    {
        echo "TEST 1: Admin legacy parameters are parsed... ";

        try {
            $request = new Request('POST', [], [
                '_estado' => 'A',
                '_login' => 'testuser',
                '_nombre1' => 'Juan',
                '_apellido1' => 'Pérez',
                '_mail' => 'juan@example.com',
                '_id_perfil' => '1',
                '_id' => '123'
            ], [], []);

            if ($request->input('estado', 'A') !== 'A') {
                throw new \Exception('estado mismatch');
            }

            if ($request->input('login', '') !== 'testuser') {
                throw new \Exception('login mismatch');
            }

            if ((int) $request->input('id', 0) !== 123) {
                throw new \Exception('id mismatch');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testParameterDefaults(): void
    {
        echo "TEST 2: Admin parameter defaults are preserved... ";

        try {
            $request = new Request('POST', [], [], [], []);

            if ($request->input('estado', 'A') !== 'A') {
                throw new \Exception('estado default mismatch');
            }

            if ((int) $request->input('id_perfil', 1) !== 1) {
                throw new \Exception('id_perfil default mismatch');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testActionParameter(): void
    {
        echo "TEST 3: Admin action is detected... ";

        try {
            $request = new Request('POST', [], ['action' => 'OBTENER_USUARIOS'], [], []);

            if ($request->action() !== 'OBTENER_USUARIOS') {
                throw new \Exception('action mismatch');
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

(new AdminParametersTest())->run();
