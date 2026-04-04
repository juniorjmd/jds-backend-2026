<?php
declare(strict_types=1);

namespace App\Modules\Documentos\Services;

use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;

class DocumentosService
{
    private Request $request;
    private AuthContext $authContext;

    public function __construct(Request $request, AuthContext $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    public function listDocuments(int $userId, string $type = ''): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar con BD y permisos reales
        return [
            [
                'documento_id' => 1,
                'nombre' => 'factura-001.pdf',
                'tipo' => $type ?: 'factura',
                'usuario_id' => $userId,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]
        ];
    }

    public function uploadDocument(int $userId, string $name, string $type, string $contentBase64): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        if (empty($name) || empty($type) || empty($contentBase64)) {
            throw new \Exception('Faltan datos requeridos para subir el documento');
        }

        // TODO: Implementar almacenamiento real de archivo
        return [
            'documento_id' => rand(100, 999),
            'nombre' => $name,
            'tipo' => $type,
            'usuario_id' => $userId,
            'url_descarga' => "https://example.com/download/{$name}",
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];
    }

    public function downloadDocument(int $documentId): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        if ($documentId <= 0) {
            throw new \Exception('ID de documento inválido');
        }

        // TODO: Implementar búsqueda real en BD
        return [
            'documento_id' => $documentId,
            'nombre' => 'factura-001.pdf',
            'tipo' => 'factura',
            'download_url' => "https://example.com/download/{$documentId}",
            'usuario_id' => 1
        ];
    }

    public function deleteDocument(int $documentId): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        if ($documentId <= 0) {
            throw new \Exception('ID de documento inválido');
        }

        // TODO: Implementar eliminación física o lógica
        return [
            'documento_id' => $documentId,
            'deleted' => true,
            'fecha_eliminacion' => date('Y-m-d H:i:s')
        ];
    }
}
