<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;
use App\Modules\Documentos\Services\DocumentosService;

class DocumentosServiceAuthContextStub
{
    public function __construct(private bool $success = true)
    {
    }

    public function resolve(Request $request): array
    {
        return $this->success ? ['success' => true, 'user_id' => 123] : ['success' => false];
    }
}

class DocumentosServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== DOCUMENTOS SERVICE TEST ==========\n\n";

        $this->testListDocumentsWithValidAuth();
        $this->testListDocumentsWithInvalidAuth();
        $this->testUploadDocumentWithValidData();
        $this->testUploadDocumentWithMissingData();
        $this->testDownloadDocumentWithValidId();
        $this->testDeleteDocumentWithInvalidId();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function service(bool $success = true): DocumentosService
    {
        return new DocumentosService(new Request('POST', [], [], [], []), new DocumentosServiceAuthContextStub($success));
    }

    private function testListDocumentsWithValidAuth(): void
    {
        echo "TEST 1: listDocuments returns data with auth... ";

        try {
            $result = $this->service()->listDocuments(123, 'factura');
            if (!is_array($result) || ($result[0]['tipo'] ?? '') !== 'factura') {
                throw new \Exception('invalid list result');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testListDocumentsWithInvalidAuth(): void
    {
        echo "TEST 2: listDocuments requires auth... ";

        try {
            $this->service(false)->listDocuments(123, 'factura');
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Usuario no autenticado') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testUploadDocumentWithValidData(): void
    {
        echo "TEST 3: uploadDocument returns metadata... ";

        try {
            $result = $this->service()->uploadDocument(123, 'test.pdf', 'factura', 'base64data');
            if (($result['nombre'] ?? '') !== 'test.pdf') {
                throw new \Exception('invalid upload result');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testUploadDocumentWithMissingData(): void
    {
        echo "TEST 4: uploadDocument validates required data... ";

        try {
            $this->service()->uploadDocument(123, '', 'factura', '');
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Faltan datos requeridos para subir el documento') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testDownloadDocumentWithValidId(): void
    {
        echo "TEST 5: downloadDocument returns metadata... ";

        try {
            $result = $this->service()->downloadDocument(456);
            if (($result['documento_id'] ?? 0) !== 456) {
                throw new \Exception('invalid download result');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testDeleteDocumentWithInvalidId(): void
    {
        echo "TEST 6: deleteDocument rejects invalid id... ";

        try {
            $this->service()->deleteDocument(0);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'ID de documento inválido') {
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

(new DocumentosServiceTest())->run();
