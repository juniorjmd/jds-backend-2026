<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;
use App\Modules\Documentos\Repositories\DocumentosRepository;
use App\Modules\Documentos\Services\DocumentosService;

class DocumentosServiceAuthContextStub
{
    public function __construct(private bool $success = true)
    {
    }

    public function resolve(Request $request): array
    {
        return $this->success
            ? ['success' => true, 'compact_user' => ['id' => 123]]
            : ['success' => false, 'message' => 'Usuario no autenticado', 'status' => 401];
    }
}

class DocumentosRepositoryStub extends DocumentosRepository
{
    public function __construct(
        private array $userDocuments = [],
        private array $createdDocuments = [],
        private array $createdPurchaseDocuments = [],
        private array $changedDocuments = [],
        private array $changedPurchaseDocuments = [],
        private array $expenseDocuments = [],
        private array $abonoDocuments = [],
        private array $abonoPayableDocuments = [],
        private array $devolucionDocuments = [],
        private array $notaDebitoDocuments = [],
        private array $procedureResults = [],
        private array $documentRows = [],
        private array $documentLines = [],
        private array $documentViews = [],
        private array $creditMovements = []
    ) {
    }

    public function beginTransaction(): bool { return true; }
    public function commitTransaction(): bool { return true; }
    public function rollBackTransaction(): bool { return true; }

    public function getUserDocuments(int $userId, bool $validateBox): array
    {
        return $this->userDocuments;
    }

    public function createDocumentByUser(int $userId): array
    {
        return $this->createdDocuments;
    }

    public function createPurchaseDocumentByUser(int $userId, int $establishmentId): array
    {
        return $this->createdPurchaseDocuments;
    }

    public function changeActiveDocument(int $userId, int $documentId): array
    {
        return $this->changedDocuments;
    }

    public function changeActivePurchaseDocument(int $documentId): array
    {
        return $this->changedPurchaseDocuments;
    }

    public function createExpenseDocumentByUser(int $userId): array { return $this->expenseDocuments; }
    public function createCreditAbonoByUser(int $userId, int $clientId): array { return $this->abonoDocuments; }
    public function createPayableCreditAbonoByUser(int $userId, int $clientId, int $establishmentId): array { return $this->abonoPayableDocuments; }
    public function createDevolucionByUser(int $userId, int $documentId): array { return $this->devolucionDocuments; }
    public function createNotaDebitoByUser(int $userId, string $documentCode): array { return $this->notaDebitoDocuments; }
    public function returnProductFromDevolucionLine(int $lineId, float $quantity): array { return $this->procedureResults['sp_devolver_producto_devolucion'] ?? [['_result' => 100]]; }
    public function returnProductFromNotaDebitoLine(int $lineId, float $quantity): array { return $this->procedureResults['sp_devolver_producto_nota_debito'] ?? [['_result' => 100]]; }
    public function closeDocumentPayments(int $documentId): array { return $this->procedureResults['actualizaCierresPagos'] ?? [['_result' => 100]]; }
    public function updateBonosBalance(int $documentId): array { return $this->procedureResults['sp_actualizar_saldo_bonos'] ?? [['_result' => 100]]; }
    public function generateDeliveryDocument(int $documentId): array { return $this->procedureResults['generarDomicilio'] ?? [['_result' => 100]]; }
    public function cancelDocumentByUser(int $userId, int $documentId): array { return $this->procedureResults['cancelarDocumento'] ?? [['_result' => 100, 'msg' => 'Documento cancelado']]; }
    public function returnProductFromSaleLine(int $lineId): array { return $this->procedureResults['sp_devolver_producto'] ?? [['_result' => 100]]; }
    public function returnProductFromQuotationLine(int $lineId): array { return $this->procedureResults['sp_devolver_producto_cotizacion'] ?? [['_result' => 100]]; }
    public function createQuotationByUser(int $userId, int $documentId): array { return $this->procedureResults['SP_CREAR_DOCUMENTO_COTIZACION'] ?? [['_result' => 100, 'msg' => 'Cotización creada']]; }
    public function getDocumentObjectRow(int $documentId): ?array { return $this->documentRows[$documentId] ?? null; }
    public function getDocumentLine(int $lineId): ?array { return $this->documentLines[$lineId] ?? null; }
    public function getDocumentViewByCode(string $documentCode): ?array { return $this->documentViews[$documentCode] ?? null; }
    public function getCreditMovementByInvoice(int $invoiceId): ?array { return $this->creditMovements[$invoiceId] ?? null; }
    public function insertDocumentLine(array $line): int { return 1; }
    public function updateDocumentTypeByName(int $documentId, string $typeName, bool $touchDateTime = false): bool { return true; }
    public function updateDocumentCounterByName(int $documentId, string $counterName, bool $touchDateTime = false): bool { return true; }
    public function updateExpenseDocumentMetadata(int $documentId, int $clientId, float $value): bool { return true; }
    public function updateDocumentToDelivery(int $documentId): bool { return true; }
    public function changeDocumentBox(int $boxId, int $userId, int $documentId): bool { return true; }
    public function insertAccountingOperation(int $userId, string $documentCode, string $documentName, string $documentDescription, int $documentOrder, int $clientId): int { return 900; }
    public function insertCreditMovementAbono(int $creditMovementId, int $userId, float $totalAbonos, int $comprobante): bool { return true; }
    public function insertDebitCreditTransaction(int $accountId, float $debit, float $credit, int $userId, int $operationId, string $origin, int $thirdPartyId): bool { return true; }
    public function insertPaymentReturnTransactionFromMedium(int $mediumId, float $amount, int $fallbackAccountId, int $userId, int $operationId, int $thirdPartyId): bool { return true; }
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
        $this->testGetCurrentUserDocumentsReturnsStandardCollection();
        $this->testCreateCurrentUserDocumentParsesProcedurePayload();
        $this->testChangeCurrentUserDocumentReturnsMessage();
        $this->testCreateExpenseDocumentReturnsDocumentoFinal();
        $this->testCloseInvoiceDocumentReturnsDocumentoFinal();
        $this->testCreateCreditAbonoDocumentReturnsDocumentoFinal();
        $this->testChangeDocumentBoxValidatesPayload();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function service(
        bool $success = true,
        ?DocumentosRepository $repository = null
    ): DocumentosService {
        return new DocumentosService(
            new Request('POST', [], [], [], []),
            new DocumentosServiceAuthContextStub($success),
            $repository
        );
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

    private function testGetCurrentUserDocumentsReturnsStandardCollection(): void
    {
        echo "TEST 7: getCurrentUserDocuments returns standard records/count... ";

        try {
            $repository = new DocumentosRepositoryStub(
                userDocuments: [[
                    'objeto' => json_encode([
                        ['orden' => 10, 'estado' => 1],
                        ['orden' => 11, 'estado' => 0],
                    ], JSON_UNESCAPED_UNICODE),
                ]]
            );

            $result = $this->service(true, $repository)->getCurrentUserDocuments(true);

            if (($result['count'] ?? 0) !== 2 || ($result['records'][0]['orden'] ?? 0) !== 10) {
                throw new \Exception('invalid document collection');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateCurrentUserDocumentParsesProcedurePayload(): void
    {
        echo "TEST 8: createCurrentUserDocument parses procedure payload... ";

        try {
            $repository = new DocumentosRepositoryStub(
                createdDocuments: [[
                    '_result' => 100,
                    'msg' => 'Documento creado',
                    'idIngresado' => 44,
                    'OBJ' => json_encode([
                        ['orden' => 44, 'estado' => 1],
                    ], JSON_UNESCAPED_UNICODE),
                ]]
            );

            $result = $this->service(true, $repository)->createCurrentUserDocument();

            if (($result['documentId'] ?? 0) !== 44 || ($result['count'] ?? 0) !== 1) {
                throw new \Exception('invalid creation payload');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testChangeCurrentUserDocumentReturnsMessage(): void
    {
        echo "TEST 9: changeCurrentUserDocument returns message... ";

        try {
            $repository = new DocumentosRepositoryStub(
                changedDocuments: [[
                    '_result' => 100,
                    'msg' => 'Documento cambiado',
                ]]
            );

            $result = $this->service(true, $repository)->changeCurrentUserDocument(55);

            if (($result['documentId'] ?? 0) !== 55 || ($result['message'] ?? '') !== 'Documento cambiado') {
                throw new \Exception('invalid change payload');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateExpenseDocumentReturnsDocumentoFinal(): void
    {
        echo "TEST 10: createExpenseDocument returns documentoFinal... ";

        try {
            $repository = new DocumentosRepositoryStub(
                expenseDocuments: [[
                    '_result' => 100,
                    'msg' => 'Documento gasto creado',
                    'idIngresado' => 88,
                ]],
                documentRows: [
                    88 => ['objeto' => json_encode(['orden' => 88, 'estado' => 1], JSON_UNESCAPED_UNICODE)],
                ]
            );

            $result = $this->service(true, $repository)->createExpenseDocument([
                'nombre' => 'Caja menor',
                'descripcion' => 'Compra insumos',
                'valor' => 50000,
                'idTercero' => 10,
            ]);

            if (($result['documentId'] ?? 0) !== 88 || ($result['documentoFinal']['orden'] ?? 0) !== 88) {
                throw new \Exception('invalid expense payload');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCloseInvoiceDocumentReturnsDocumentoFinal(): void
    {
        echo "TEST 11: closeInvoiceDocument returns documentoFinal... ";

        try {
            $repository = new DocumentosRepositoryStub(
                documentRows: [
                    91 => ['objeto' => json_encode(['orden' => 91, 'estado' => 1], JSON_UNESCAPED_UNICODE)],
                ]
            );

            $result = $this->service(true, $repository)->closeInvoiceDocument(91);

            if (($result['documentoFinal']['orden'] ?? 0) !== 91) {
                throw new \Exception('invalid close payload');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateCreditAbonoDocumentReturnsDocumentoFinal(): void
    {
        echo "TEST 12: createCreditAbonoDocument returns documentoFinal... ";

        try {
            $repository = new DocumentosRepositoryStub(
                abonoDocuments: [[
                    '_result' => 100,
                    'msg' => 'Abono creado',
                    'idIngresado' => 77,
                ]],
                documentRows: [
                    77 => ['objeto' => json_encode(['orden' => 77, 'estado' => 1], JSON_UNESCAPED_UNICODE)],
                ]
            );

            $result = $this->service(true, $repository)->createCreditAbonoDocument([
                'cliente' => 9,
                'listado' => [[
                    'idDocBase' => 1,
                    'idProducto' => 'ABONO',
                    'nombreProducto' => 'Abono prueba',
                    'presioVenta' => 10000,
                    'id_externo_auxiliar' => 4,
                ]],
            ]);

            if (($result['documentId'] ?? 0) !== 77 || ($result['documentoFinal']['orden'] ?? 0) !== 77) {
                throw new \Exception('invalid abono payload');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testChangeDocumentBoxValidatesPayload(): void
    {
        echo "TEST 13: changeDocumentBox validates payload... ";

        try {
            $result = $this->service(true, new DocumentosRepositoryStub())->changeDocumentBox([
                'id' => 2,
                'usuarioEstadoCaja' => 5,
                'documentoActivoCaja' => 44,
            ]);

            if (($result['documentId'] ?? 0) !== 44 || ($result['affected'] ?? 0) !== 1) {
                throw new \Exception('invalid box change payload');
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

(new DocumentosServiceTest())->run();
