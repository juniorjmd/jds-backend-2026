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
        } catch (\Exception $e) {
            Response::fail('LIST_DOCUMENTS_ERROR', $e->getMessage());
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
        } catch (\Exception $e) {
            Response::fail('UPLOAD_DOCUMENT_ERROR', $e->getMessage());
        }
    }

    public function downloadDocument(): void
    {
        try {
            $result = $this->service->downloadDocument(
                documentId: (int) $this->request->input('documento_id', 0)
            );

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('DOWNLOAD_DOCUMENT_ERROR', $e->getMessage());
        }
    }

    public function deleteDocument(): void
    {
        try {
            $result = $this->service->deleteDocument(
                documentId: (int) $this->request->input('documento_id', 0)
            );

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('DELETE_DOCUMENT_ERROR', $e->getMessage());
        }
    }
}
