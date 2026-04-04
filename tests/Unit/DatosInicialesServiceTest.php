<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Modules\DatosIniciales\Repositories\DatosInicialesRepository;
use App\Modules\DatosIniciales\Services\DatosInicialesService;

class DatosInicialesServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== DATOS INICIALES SERVICE TEST ==========\n\n";

        $this->testGetPrincipalBranchDataReturnsRows();
        $this->testEmptyPrincipalBranchFails();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testGetPrincipalBranchDataReturnsRows(): void
    {
        echo "TEST 1: getPrincipalBranchData returns rows... ";

        try {
            $repository = new class extends DatosInicialesRepository {
                public function __construct()
                {
                }

                public function findPrincipalBranchByDescription(string $description): array
                {
                    return [
                        ['id' => 1, 'descripcion' => $description, 'nombre' => 'Principal'],
                    ];
                }
            };

            $service = new DatosInicialesService($repository);
            $result = $service->getPrincipalBranchData();

            if (($result[0]['nombre'] ?? '') !== 'Principal') {
                throw new \Exception('Unexpected branch name');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testEmptyPrincipalBranchFails(): void
    {
        echo "TEST 2: empty principal branch result is rejected... ";

        try {
            $repository = new class extends DatosInicialesRepository {
                public function __construct()
                {
                }

                public function findPrincipalBranchByDescription(string $description): array
                {
                    return [];
                }
            };

            $service = new DatosInicialesService($repository);
            $service->getPrincipalBranchData();
            throw new \Exception('Expected exception was not thrown');
        } catch (\Throwable $e) {
            if ($e->getMessage() !== 'Error de datos, No existen valores iniciales para consultar') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
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

(new DatosInicialesServiceTest())->run();
