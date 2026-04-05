<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class DocumentosParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== DOCUMENTOS PARAMETERS TEST ==========\n\n";

        $this->testDocumentosActionsMapping();
        $this->testRoutesLoadsDocumentosActions();
        $this->testDocumentosControllerExists();
        $this->testLegacyParametersFallback();
        $this->testActionDetected();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testDocumentosActionsMapping(): void
    {
        echo "TEST 1: Documentos actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/documentos-actions.php';
            foreach ([
                'GET_DOCUMENTOS_USUARIO_ACTUAL',
                'GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA',
                'CREAR_DOCUMENTO_POR_USUARIO',
                'CREAR_DOCUMENTO_COMPRA_POR_USUARIO',
                'CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO',
                'CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO',
                'CREAR_DOCUMENTO_GASTO_POR_USUARIO',
                'ASIGNAR_ABONO_DOCUMENTOS_CREDITO',
                'ASIGNAR_ABONO_DOCUMENTOS_CREDITO_POR_PAGAR',
                'GENERAR_DOCUMENTOS_DEVOLUCION',
                'GENERAR_DOCUMENTOS_NOTA_DEBITO',
                'CERRAR_DOCUMENTO_FACTURA',
                'CERRAR_DOCUMENTO_REMISION',
                'CAMBIAR_DOCUMENTO_A_ENVIO',
                'CANCELAR_DOCUMENTO_POR_USUARIO',
                'CREAR_DOCUMENTO_COTIZACION_POR_USUARIO',
                'CAMBIAR_DOCUMENTO_POR_CAJA',
                'LISTAR_DOCUMENTOS',
                'SUBIR_DOCUMENTO',
                'DESCARGAR_DOCUMENTO',
                'BORRAR_DOCUMENTO',
            ] as $action) {
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

    private function testRoutesLoadsDocumentosActions(): void
    {
        echo "TEST 2: Routes loads documentos actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();
            foreach ([
                'GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA',
                'CERRAR_DOCUMENTO_FACTURA',
                'CREAR_DOCUMENTO_GASTO_POR_USUARIO',
            ] as $action) {
                if (!array_key_exists($action, $map)) {
                    throw new \Exception("Action {$action} not found in Routes::map()");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testDocumentosControllerExists(): void
    {
        echo "TEST 3: DocumentosController has required methods... ";

        try {
            $controller = \App\Modules\Documentos\DocumentosController::class;
            foreach ([
                'getCurrentUserDocuments',
                'getCurrentUserDocumentsByActiveBox',
                'createCurrentUserDocument',
                'createCurrentUserPurchaseDocument',
                'changeCurrentUserDocument',
                'changeCurrentUserPurchaseDocument',
                'createExpenseDocument',
                'createCreditAbonoDocument',
                'createPayableCreditAbonoDocument',
                'createDevolucionDocument',
                'createNotaDebitoDocument',
                'closeInvoiceDocument',
                'closeRemisionDocument',
                'sendDocumentToDelivery',
                'cancelDocument',
                'convertDocumentToQuotation',
                'changeDocumentBox',
            ] as $method) {
                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method $method not found");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testLegacyParametersFallback(): void
    {
        echo "TEST 4: Documentos accepts legacy parameters... ";

        try {
            $request = new \App\Core\Http\Request('POST', [], [
                '_usuario_id' => 123,
                '_tipo' => 'factura',
                '_documento_id' => 456,
            ], [], []);

            if ($request->input('usuario_id', 0) !== 123) {
                throw new \Exception('usuario_id mismatch');
            }
            if ($request->input('tipo', '') !== 'factura') {
                throw new \Exception('tipo mismatch');
            }
            if ($request->input('documento_id', 0) !== 456) {
                throw new \Exception('documento_id mismatch');
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
            $request = new \App\Core\Http\Request('POST', [], ['action' => 'CERRAR_DOCUMENTO_FACTURA'], [], []);
            if ($request->action() !== 'CERRAR_DOCUMENTO_FACTURA') {
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

(new DocumentosParametersTest())->run();
