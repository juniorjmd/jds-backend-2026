<?php
declare(strict_types=1);

namespace App\Modules\Documentos;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Documentos\Services\DocumentosService;

class DocumentosController
{
    private Request $request;
    private DocumentosService $service;

    public function __construct(
        Request $request,
        DocumentosService $service
    ) {
        $this->request = $request;
        $this->service = $service;
    }

    public function listDocuments(): void
    {
        try {
            $result = $this->service->listDocuments(
                userId: (int) $this->request->input('usuario_id', 0),
                type: $this->request->input('tipo', '')
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('LIST_DOCUMENTS_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function uploadDocument(): void
    {
        try {
            $result = $this->service->uploadDocument(
                userId: (int) $this->request->input('usuario_id', 0),
                name: $this->request->input('nombre', ''),
                type: $this->request->input('tipo', ''),
                contentBase64: $this->request->input('contenido_base64', '')
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('UPLOAD_DOCUMENT_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function downloadDocument(): void
    {
        try {
            $result = $this->service->downloadDocument(
                documentId: (int) $this->request->input('documento_id', 0)
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('DOWNLOAD_DOCUMENT_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function deleteDocument(): void
    {
        try {
            $result = $this->service->deleteDocument(
                documentId: (int) $this->request->input('documento_id', 0)
            );

            Response::ok($result);
        } catch (\Throwable $e) {
            Response::fail('DELETE_DOCUMENT_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function getCurrentUserDocuments(): void
    {
        try {
            Response::ok($this->service->getCurrentUserDocuments(false));
        } catch (\Throwable $e) {
            Response::fail('GET_DOCUMENTOS_USUARIO_ACTUAL_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function getCurrentUserDocumentsByActiveBox(): void
    {
        try {
            Response::ok($this->service->getCurrentUserDocuments(true));
        } catch (\Throwable $e) {
            Response::fail('GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function createCurrentUserDocument(): void
    {
        try {
            Response::ok($this->service->createCurrentUserDocument());
        } catch (\Throwable $e) {
            Response::fail('CREAR_DOCUMENTO_POR_USUARIO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function createCurrentUserPurchaseDocument(): void
    {
        try {
            $establishmentId = (int) $this->request->input('_establecimiento', 0);
            Response::ok($this->service->createCurrentUserPurchaseDocument($establishmentId));
        } catch (\Throwable $e) {
            Response::fail('CREAR_DOCUMENTO_COMPRA_POR_USUARIO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function changeCurrentUserDocument(): void
    {
        try {
            $documentId = (int) $this->request->input('_docActual', 0);
            Response::ok($this->service->changeCurrentUserDocument($documentId));
        } catch (\Throwable $e) {
            Response::fail('CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function changeCurrentUserPurchaseDocument(): void
    {
        try {
            $documentId = (int) $this->request->input('_docActual', 0);
            Response::ok($this->service->changeCurrentUserPurchaseDocument($documentId));
        } catch (\Throwable $e) {
            Response::fail('CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function createExpenseDocument(): void
    {
        try {
            Response::ok($this->service->createExpenseDocument((array) $this->request->input('_arraydatos', [])));
        } catch (\Throwable $e) {
            Response::fail('CREAR_DOCUMENTO_GASTO_POR_USUARIO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function createCreditAbonoDocument(): void
    {
        try {
            Response::ok($this->service->createCreditAbonoDocument((array) $this->request->input('_documentoAbono', [])));
        } catch (\Throwable $e) {
            Response::fail('ASIGNAR_ABONO_DOCUMENTOS_CREDITO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function createPayableCreditAbonoDocument(): void
    {
        try {
            Response::ok($this->service->createPayableCreditAbonoDocument((array) $this->request->input('_documentoAbono', [])));
        } catch (\Throwable $e) {
            Response::fail('ASIGNAR_ABONO_DOCUMENTOS_CREDITO_POR_PAGAR_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function createDevolucionDocument(): void
    {
        try {
            Response::ok($this->service->createDevolucionDocument((array) $this->request->input('_documentoAbono', [])));
        } catch (\Throwable $e) {
            Response::fail('GENERAR_DOCUMENTOS_DEVOLUCION_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function createNotaDebitoDocument(): void
    {
        try {
            Response::ok($this->service->createNotaDebitoDocument((array) $this->request->input('_documentoAbono', [])));
        } catch (\Throwable $e) {
            Response::fail('GENERAR_DOCUMENTOS_NOTA_DEBITO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function closeInvoiceDocument(): void
    {
        try {
            Response::ok($this->service->closeInvoiceDocument((int) $this->request->input('_documento', 0)));
        } catch (\Throwable $e) {
            Response::fail('CERRAR_DOCUMENTO_FACTURA_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function closeRemisionDocument(): void
    {
        try {
            Response::ok($this->service->closeRemisionDocument((int) $this->request->input('_documento', 0)));
        } catch (\Throwable $e) {
            Response::fail('CERRAR_DOCUMENTO_REMISION_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function sendDocumentToDelivery(): void
    {
        try {
            Response::ok($this->service->sendDocumentToDelivery((int) $this->request->input('_documento', 0)));
        } catch (\Throwable $e) {
            Response::fail('CAMBIAR_DOCUMENTO_A_ENVIO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function cancelDocument(): void
    {
        try {
            Response::ok($this->service->cancelDocument((int) $this->request->input('_documento', 0)));
        } catch (\Throwable $e) {
            Response::fail('CANCELAR_DOCUMENTO_POR_USUARIO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function convertDocumentToQuotation(): void
    {
        try {
            Response::ok($this->service->convertDocumentToQuotation((int) $this->request->input('_documento', 0)));
        } catch (\Throwable $e) {
            Response::fail('CREAR_DOCUMENTO_COTIZACION_POR_USUARIO_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    public function changeDocumentBox(): void
    {
        try {
            Response::ok($this->service->changeDocumentBox((array) $this->request->input('datos', [])));
        } catch (\Throwable $e) {
            Response::fail('CAMBIAR_DOCUMENTO_POR_CAJA_ERROR', $e->getMessage(), $this->resolveStatus($e));
        }
    }

    private function resolveStatus(\Throwable $e): int
    {
        $status = (int) $e->getCode();
        return $status >= 400 && $status <= 599 ? $status : 400;
    }
}
