<?php
declare(strict_types=1);

namespace App\Modules\Documentos\Services;

use App\Core\Http\Request;
use App\Modules\Documentos\Repositories\DocumentosRepository;

class DocumentosService
{
    public function __construct(
        private Request $request,
        private $authContext,
        private ?DocumentosRepository $repository = null
    ) {
    }

    public function listDocuments(int $userId, string $type = ''): array
    {
        $this->requireAuthenticatedUserId();

        return [[
            'documento_id' => 1,
            'nombre' => 'factura-001.pdf',
            'tipo' => $type ?: 'factura',
            'usuario_id' => $userId,
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]];
    }

    public function uploadDocument(int $userId, string $name, string $type, string $contentBase64): array
    {
        $this->requireAuthenticatedUserId();

        if (empty($name) || empty($type) || empty($contentBase64)) {
            throw new \RuntimeException('Faltan datos requeridos para subir el documento', 422);
        }

        return [
            'documento_id' => rand(100, 999),
            'nombre' => $name,
            'tipo' => $type,
            'usuario_id' => $userId,
            'url_descarga' => "https://example.com/download/{$name}",
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ];
    }

    public function downloadDocument(int $documentId): array
    {
        $this->requireAuthenticatedUserId();

        if ($documentId <= 0) {
            throw new \RuntimeException('ID de documento inválido', 422);
        }

        return [
            'documento_id' => $documentId,
            'nombre' => 'factura-001.pdf',
            'tipo' => 'factura',
            'download_url' => "https://example.com/download/{$documentId}",
            'usuario_id' => 1,
        ];
    }

    public function deleteDocument(int $documentId): array
    {
        $this->requireAuthenticatedUserId();

        if ($documentId <= 0) {
            throw new \RuntimeException('ID de documento inválido', 422);
        }

        return [
            'documento_id' => $documentId,
            'deleted' => true,
            'fecha_eliminacion' => date('Y-m-d H:i:s'),
        ];
    }

    public function getCurrentUserDocuments(bool $validateBox): array
    {
        $userId = $this->requireAuthenticatedUserId();
        $rows = $this->repository()->getUserDocuments($userId, $validateBox);
        $documents = $this->decodeDocumentCollection($rows, 'objeto');

        return [
            'records' => $documents,
            'count' => count($documents),
        ];
    }

    public function createCurrentUserDocument(): array
    {
        $userId = $this->requireAuthenticatedUserId();
        $rows = $this->repository()->createDocumentByUser($userId);

        return $this->normalizeDocumentCreationResult($rows, 'Documento de venta creado correctamente');
    }

    public function createCurrentUserPurchaseDocument(int $establishmentId): array
    {
        if ($establishmentId <= 0) {
            throw new \RuntimeException('Error de datos, faltan uno o más valores para la consulta', 422);
        }

        $userId = $this->requireAuthenticatedUserId();
        $rows = $this->repository()->createPurchaseDocumentByUser($userId, $establishmentId);

        return $this->normalizeDocumentCreationResult($rows, 'Documento de compra creado correctamente');
    }

    public function changeCurrentUserDocument(int $documentId): array
    {
        if ($documentId <= 0) {
            throw new \RuntimeException('Error de datos, faltan uno o más valores para la consulta', 422);
        }

        $userId = $this->requireAuthenticatedUserId();
        $rows = $this->repository()->changeActiveDocument($userId, $documentId);
        $firstRow = $this->assertProcedureSuccess($rows, 'cambiarDocumentoActual');

        return [
            'message' => $this->procedureMessage($firstRow, 'Documento activo actualizado correctamente'),
            'documentId' => $documentId,
        ];
    }

    public function changeCurrentUserPurchaseDocument(int $documentId): array
    {
        if ($documentId <= 0) {
            throw new \RuntimeException('Error de datos, faltan uno o más valores para la consulta', 422);
        }

        $this->requireAuthenticatedUserId();
        $rows = $this->repository()->changeActivePurchaseDocument($documentId);
        $firstRow = $this->assertProcedureSuccess($rows, 'cambiarDocumentoCompraActual');

        return [
            'message' => $this->procedureMessage($firstRow, 'Documento de compra activo actualizado correctamente'),
            'documentId' => $documentId,
        ];
    }

    public function createExpenseDocument(array $payload): array
    {
        $this->assertRequiredKeys($payload, ['nombre', 'descripcion', 'valor', 'idTercero']);

        $userId = $this->requireAuthenticatedUserId();
        $repository = $this->repository();
        $repository->beginTransaction();

        try {
            $documentId = $this->extractProcedureDocumentId(
                $repository->createExpenseDocumentByUser($userId),
                'crearNuevoDocumentoGasto'
            );

            $repository->insertDocumentLine([
                'orden' => $documentId,
                'idDocumento' => $documentId,
                'idProducto' => 'Gasto-000001',
                'nombreProducto' => sprintf('Gasto : %s => Descripcion : %s', (string) $payload['nombre'], (string) $payload['descripcion']),
                'presioVenta' => (float) $payload['valor'],
                'porcent_iva' => 0,
                'presioSinIVa' => (float) $payload['valor'],
                'IVA' => 0,
                'cantidadVendida' => 1,
                'valorTotal' => (float) $payload['valor'],
                'usuario' => $userId,
                'cant_real_descontada' => 1,
                'id_existencia' => 999999999,
                'estado_linea_venta' => 'G',
            ]);

            $repository->updateExpenseDocumentMetadata($documentId, (int) $payload['idTercero'], (float) $payload['valor']);
            $documentoFinal = $this->fetchDocumentObject($documentId);
            $repository->commitTransaction();

            return $this->buildDocumentActionResponse('Documento de gasto creado correctamente', $documentId, $documentoFinal);
        } catch (\Throwable $e) {
            $repository->rollBackTransaction();
            throw $e;
        }
    }

    public function createCreditAbonoDocument(array $payload): array
    {
        return $this->createAbonoDocument($payload, false);
    }

    public function createPayableCreditAbonoDocument(array $payload): array
    {
        return $this->createAbonoDocument($payload, true);
    }

    public function createDevolucionDocument(array $payload): array
    {
        $this->assertRequiredKeys($payload, ['idDocumentoFinal', 'listado']);
        $this->assertListPayload($payload['listado']);

        $userId = $this->requireAuthenticatedUserId();
        $repository = $this->repository();
        $repository->beginTransaction();

        try {
            $documentId = $this->extractProcedureDocumentId(
                $repository->createDevolucionByUser($userId, (int) $payload['idDocumentoFinal']),
                'crearNuevoDocumentoDevolucion'
            );

            foreach ((array) $payload['listado'] as $item) {
                $quantity = (float) ($item['cant_devuelta'] ?? 0);
                if ($quantity <= 0) {
                    continue;
                }

                $this->assertProcedureSuccess(
                    $repository->returnProductFromDevolucionLine((int) $item['id'], $quantity),
                    'sp_devolver_producto_devolucion'
                );

                $repository->insertDocumentLine([
                    'orden' => $documentId,
                    'idDocumento' => $documentId,
                    'idProducto' => $item['idProducto'],
                    'nombreProducto' => $item['nombreProducto'],
                    'presioVenta' => (float) $item['presioVenta'],
                    'porcent_iva' => (float) ($item['porcent_iva'] ?? 0),
                    'presioSinIVa' => (float) ($item['presioSinIVa'] ?? 0),
                    'IVA' => (float) ($item['IVA'] ?? 0),
                    'cantidadVendida' => $quantity,
                    'valorTotal' => (float) $item['presioVenta'] * $quantity,
                    'usuario' => $userId,
                    'cant_real_descontada' => $quantity,
                    'id_existencia' => (int) ($item['id_existencia'] ?? 0),
                    'estado_linea_venta' => 'D',
                ]);
            }

            $repository->updateDocumentCounterByName($documentId, 'comprobante_devolucion', true);
            $documentoFinal = $this->fetchDocumentObject($documentId);
            $repository->commitTransaction();

            return $this->buildDocumentActionResponse('Documento de devolución generado correctamente', $documentId, $documentoFinal);
        } catch (\Throwable $e) {
            $repository->rollBackTransaction();
            throw $e;
        }
    }

    public function createNotaDebitoDocument(array $payload): array
    {
        $this->assertRequiredKeys($payload, ['idDocF', 'pdrReturn', 'payReturn', 'exedente']);
        $this->assertListPayload($payload['pdrReturn']);

        $userId = $this->requireAuthenticatedUserId();
        $repository = $this->repository();
        $repository->beginTransaction();

        try {
            $documentId = $this->extractProcedureDocumentId(
                $repository->createNotaDebitoByUser($userId, (string) $payload['idDocF']),
                'crearNuevoDocumentoNotaDebito'
            );

            $totalReturned = 0.0;
            foreach ((array) $payload['pdrReturn'] as $item) {
                $quantity = (float) ($item['cnt'] ?? 0);
                if ($quantity <= 0) {
                    continue;
                }

                $line = $repository->getDocumentLine((int) $item['id']);
                if ($line === null) {
                    throw new \RuntimeException('Error al obtener la línea del documento enviada', 404);
                }

                $this->assertProcedureSuccess(
                    $repository->returnProductFromNotaDebitoLine((int) $item['id'], $quantity),
                    'sp_devolver_producto_nota_debito'
                );

                $unitPrice = (float) ($line['presioVenta'] ?? 0);
                $totalReturned += $unitPrice * $quantity;

                $repository->insertDocumentLine([
                    'orden' => $documentId,
                    'idDocumento' => $documentId,
                    'idProducto' => $line['idProducto'],
                    'nombreProducto' => $line['nombreProducto'],
                    'presioVenta' => $unitPrice,
                    'porcent_iva' => (float) ($line['porcent_iva'] ?? 0),
                    'presioSinIVa' => (float) ($line['presioSinIVa'] ?? 0),
                    'IVA' => (float) ($line['IVA'] ?? 0),
                    'cantidadVendida' => $quantity,
                    'valorTotal' => $unitPrice * $quantity,
                    'usuario' => $userId,
                    'cant_real_descontada' => $quantity,
                    'id_existencia' => (int) ($line['id_existencia'] ?? 0),
                    'estado_linea_venta' => 'D',
                ]);
            }

            $repository->updateDocumentCounterByName($documentId, 'comprobante_nota_debito');
            $sourceDocument = $repository->getDocumentViewByCode((string) $payload['idDocF']);

            if ($sourceDocument !== null) {
                $operationId = $repository->insertAccountingOperation(
                    $userId,
                    (string) $payload['idDocF'],
                    (string) ($sourceDocument['nombre'] ?? ''),
                    (string) ($sourceDocument['descripcion'] ?? ''),
                    (int) ($sourceDocument['orden'] ?? 0),
                    (int) ($sourceDocument['cliente'] ?? 0)
                );

                $excedente = (float) ($payload['exedente'] ?? 0);
                if ($excedente > 0) {
                    $repository->insertDebitCreditTransaction(
                        (int) ($sourceDocument['idCCntCPagar'] ?? 0),
                        0,
                        $excedente,
                        $userId,
                        $operationId,
                        'Nota Debito',
                        (int) ($sourceDocument['cliente'] ?? 0)
                    );
                }

                $creditMovement = $repository->getCreditMovementByInvoice((int) ($sourceDocument['orden'] ?? 0));
                if ($creditMovement !== null) {
                    $repository->insertCreditMovementAbono(
                        (int) $creditMovement['id'],
                        $userId,
                        $totalReturned - $excedente,
                        $documentId
                    );
                }

                foreach ((array) ($payload['payReturn'] ?? []) as $item) {
                    $amount = (float) ($item['cnt'] ?? 0);
                    if ($amount <= 0) {
                        continue;
                    }

                    $repository->insertPaymentReturnTransactionFromMedium(
                        (int) $item['id'],
                        $amount,
                        (int) ($sourceDocument['idCCntCajaGeneral'] ?? 0),
                        $userId,
                        $operationId,
                        (int) ($sourceDocument['cliente'] ?? 0)
                    );
                }
            }

            $documentoFinal = $this->fetchDocumentObject($documentId);
            $repository->commitTransaction();

            return $this->buildDocumentActionResponse('Documento de nota débito generado correctamente', $documentId, $documentoFinal);
        } catch (\Throwable $e) {
            $repository->rollBackTransaction();
            throw $e;
        }
    }

    public function closeInvoiceDocument(int $documentId): array
    {
        return $this->closeDocumentByType($documentId, 'venta', true, 'Documento de factura cerrado correctamente');
    }

    public function closeRemisionDocument(int $documentId): array
    {
        return $this->closeDocumentByType($documentId, 'remision', false, 'Documento de remisión cerrado correctamente');
    }

    public function sendDocumentToDelivery(int $documentId): array
    {
        $this->requireValidDocumentId($documentId);
        $repository = $this->repository();
        $repository->beginTransaction();

        try {
            $repository->updateDocumentToDelivery($documentId);
            $this->assertProcedureSuccess($repository->generateDeliveryDocument($documentId), 'generarDomicilio');
            $repository->commitTransaction();

            return [
                'message' => 'Documento enviado a domicilio correctamente',
                'documentId' => $documentId,
                'affected' => 1,
            ];
        } catch (\Throwable $e) {
            $repository->rollBackTransaction();
            throw $e;
        }
    }

    public function cancelDocument(int $documentId): array
    {
        $this->requireValidDocumentId($documentId);
        $userId = $this->requireAuthenticatedUserId();
        $repository = $this->repository();
        $repository->beginTransaction();

        try {
            $documentoFinal = $this->fetchDocumentObject($documentId);
            foreach ((array) ($documentoFinal['listado'] ?? []) as $line) {
                if (($line['estado_linea_venta'] ?? '') !== 'A') {
                    continue;
                }

                $this->assertProcedureSuccess(
                    $repository->returnProductFromSaleLine((int) $line['id']),
                    'sp_devolver_producto'
                );
            }

            $firstRow = $this->assertProcedureSuccess(
                $repository->cancelDocumentByUser($userId, $documentId),
                'cancelarDocumento'
            );
            $repository->commitTransaction();

            return [
                'message' => $this->procedureMessage($firstRow, 'Documento cancelado correctamente'),
                'documentId' => $documentId,
                'affected' => 1,
            ];
        } catch (\Throwable $e) {
            $repository->rollBackTransaction();
            throw $e;
        }
    }

    public function convertDocumentToQuotation(int $documentId): array
    {
        $this->requireValidDocumentId($documentId);
        $userId = $this->requireAuthenticatedUserId();
        $repository = $this->repository();
        $repository->beginTransaction();

        try {
            $documentoFinal = $this->fetchDocumentObject($documentId);
            foreach ((array) ($documentoFinal['listado'] ?? []) as $line) {
                if (($line['estado_linea_venta'] ?? '') !== 'A') {
                    continue;
                }

                $this->assertProcedureSuccess(
                    $repository->returnProductFromQuotationLine((int) $line['id']),
                    'sp_devolver_producto_cotizacion'
                );
            }

            $firstRow = $this->assertProcedureSuccess(
                $repository->createQuotationByUser($userId, $documentId),
                'SP_CREAR_DOCUMENTO_COTIZACION'
            );
            $documentoFinal = $this->fetchDocumentObject($documentId);
            $repository->commitTransaction();

            return $this->buildDocumentActionResponse(
                $this->procedureMessage($firstRow, 'Documento convertido en cotización correctamente'),
                $documentId,
                $documentoFinal
            );
        } catch (\Throwable $e) {
            $repository->rollBackTransaction();
            throw $e;
        }
    }

    public function changeDocumentBox(array $payload): array
    {
        $this->assertRequiredKeys($payload, ['id', 'usuarioEstadoCaja', 'documentoActivoCaja']);

        $boxId = (int) $payload['id'];
        $userId = (int) $payload['usuarioEstadoCaja'];
        $documentId = (int) $payload['documentoActivoCaja'];

        if ($boxId <= 0 || $userId <= 0 || $documentId <= 0) {
            throw new \RuntimeException('Error de datos, faltan uno o más valores para la consulta', 422);
        }

        $this->repository()->changeDocumentBox($boxId, $userId, $documentId);

        return [
            'message' => 'Documento cambiado de caja correctamente',
            'documentId' => $documentId,
            'affected' => 1,
        ];
    }

    private function requireAuthenticatedUserId(): int
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \RuntimeException(
                (string) ($authResult['message'] ?? 'Usuario no autenticado'),
                (int) ($authResult['status'] ?? 401)
            );
        }

        $userId = (int) ($authResult['compact_user']['id'] ?? $authResult['user_id'] ?? 0);
        if ($userId <= 0) {
            throw new \RuntimeException('Usuario no autenticado', 401);
        }

        return $userId;
    }

    private function normalizeDocumentCreationResult(array $rows, string $defaultMessage): array
    {
        $firstRow = $this->assertProcedureSuccess($rows, 'crearDocumento');
        $documents = $this->decodeDocumentCollection($rows, 'OBJ');
        $documentId = isset($firstRow['idIngresado']) ? (int) $firstRow['idIngresado'] : $this->detectDocumentId($documents);

        return [
            'message' => $this->procedureMessage($firstRow, $defaultMessage),
            'documentId' => $documentId,
            'records' => $documents,
            'count' => count($documents),
        ];
    }

    private function createAbonoDocument(array $payload, bool $payable): array
    {
        $this->assertRequiredKeys($payload, ['cliente', 'listado']);
        if ($payable) {
            $this->assertRequiredKeys($payload, ['establecimiento']);
        }
        $this->assertListPayload($payload['listado']);

        $userId = $this->requireAuthenticatedUserId();
        $repository = $this->repository();
        $repository->beginTransaction();

        try {
            $documentId = $this->extractProcedureDocumentId(
                $payable
                    ? $repository->createPayableCreditAbonoByUser($userId, (int) $payload['cliente'], (int) $payload['establecimiento'])
                    : $repository->createCreditAbonoByUser($userId, (int) $payload['cliente']),
                $payable ? 'crearNuevoDocumentoAbonoCxP' : 'crearNuevoDocumentoAbonoCxC'
            );

            foreach ((array) $payload['listado'] as $item) {
                $price = (float) ($item['presioVenta'] ?? 0);
                if ($price <= 0) {
                    continue;
                }

                $repository->insertDocumentLine([
                    'idDocBase' => (int) ($item['idDocBase'] ?? 0),
                    'orden' => $documentId,
                    'idDocumento' => $documentId,
                    'idProducto' => $item['idProducto'],
                    'nombreProducto' => $item['nombreProducto'],
                    'presioVenta' => $price,
                    'porcent_iva' => 0,
                    'presioSinIVa' => $price,
                    'IVA' => 0,
                    'id_externo_auxiliar' => $item['id_externo_auxiliar'] ?? null,
                    'cantidadVendida' => 1,
                    'valorTotal' => $price,
                    'usuario' => $userId,
                    'cant_real_descontada' => 1,
                    'id_existencia' => 999999999,
                    'estado_linea_venta' => 'C',
                ]);
            }

            $documentoFinal = $this->fetchDocumentObject($documentId);
            $repository->commitTransaction();

            return $this->buildDocumentActionResponse(
                $payable ? 'Documento de abono por pagar creado correctamente' : 'Documento de abono creado correctamente',
                $documentId,
                $documentoFinal
            );
        } catch (\Throwable $e) {
            $repository->rollBackTransaction();
            throw $e;
        }
    }

    private function closeDocumentByType(int $documentId, string $typeName, bool $touchDateTime, string $message): array
    {
        $this->requireValidDocumentId($documentId);
        $repository = $this->repository();
        $repository->beginTransaction();

        try {
            if ($repository->getDocumentObjectRow($documentId) === null) {
                throw new \RuntimeException('Error al obtener los datos del documento enviado !!!', 404);
            }

            $repository->updateDocumentTypeByName($documentId, $typeName, $touchDateTime);
            $this->assertProcedureSuccess($repository->closeDocumentPayments($documentId), 'actualizaCierresPagos');
            $this->assertProcedureSuccess($repository->updateBonosBalance($documentId), 'sp_actualizar_saldo_bonos');
            $documentoFinal = $this->fetchDocumentObject($documentId);
            $repository->commitTransaction();

            return $this->buildDocumentActionResponse($message, $documentId, $documentoFinal);
        } catch (\Throwable $e) {
            $repository->rollBackTransaction();
            throw $e;
        }
    }

    private function assertProcedureSuccess(array $rows, string $procedureName): array
    {
        if ($rows === []) {
            throw new \RuntimeException("Error de datos, Procedimiento: {$procedureName} sin respuesta", 500);
        }

        $firstRow = $rows[0];
        $procedureResult = isset($firstRow['_result']) ? (int) $firstRow['_result'] : 100;

        if ($procedureResult !== 100) {
            $message = trim((string) ($firstRow['msg'] ?? ''));
            if ($message === '') {
                $message = "Error de datos, Procedimiento: {$procedureName}";
            }

            throw new \RuntimeException($message, 400);
        }

        return $firstRow;
    }

    private function extractProcedureDocumentId(array $rows, string $procedureName): int
    {
        $firstRow = $this->assertProcedureSuccess($rows, $procedureName);
        $documentId = (int) ($firstRow['idIngresado'] ?? 0);

        if ($documentId <= 0) {
            throw new \RuntimeException("Error de datos, Procedimiento: {$procedureName} sin idIngresado", 500);
        }

        return $documentId;
    }

    private function fetchDocumentObject(int $documentId): array
    {
        $row = $this->repository()->getDocumentObjectRow($documentId);
        if ($row === null) {
            throw new \RuntimeException('Error al obtener los datos del documento enviado !!!', 404);
        }

        $decoded = json_decode((string) ($row['objeto'] ?? ''), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Error al decodificar JSON: documento inválido', 500);
        }

        return $decoded;
    }

    private function buildDocumentActionResponse(string $message, ?int $documentId = null, ?array $documentoFinal = null): array
    {
        $response = ['message' => $message, 'affected' => 1];
        if ($documentId !== null) {
            $response['documentId'] = $documentId;
        }
        if ($documentoFinal !== null) {
            $response['documentoFinal'] = $documentoFinal;
        }

        return $response;
    }

    private function requireValidDocumentId(int $documentId): void
    {
        if ($documentId <= 0) {
            throw new \RuntimeException('Error en el código del documento enviado !!!', 422);
        }
    }

    private function assertRequiredKeys(array $payload, array $keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload) || $payload[$key] === '' || $payload[$key] === null) {
                throw new \RuntimeException('Error de datos, faltan uno o más valores para la consulta', 422);
            }
        }
    }

    private function assertListPayload(mixed $list): void
    {
        if (!is_array($list) || $list === []) {
            throw new \RuntimeException('No existe informacion necesaria para generar el abono', 422);
        }
    }

    private function decodeDocumentCollection(array $rows, string $jsonColumn): array
    {
        foreach ($rows as $row) {
            if (!isset($row[$jsonColumn])) {
                continue;
            }

            $decoded = json_decode((string) $row[$jsonColumn], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Error al decodificar JSON: ' . json_last_error_msg(), 500);
            }

            return is_array($decoded) ? array_values($decoded) : [];
        }

        return [];
    }

    private function procedureMessage(array $row, string $defaultMessage): string
    {
        $message = trim((string) ($row['msg'] ?? ''));
        return $message !== '' ? $message : $defaultMessage;
    }

    private function detectDocumentId(array $documents): ?int
    {
        foreach ($documents as $document) {
            if ((int) ($document['estado'] ?? 0) === 1 && isset($document['orden'])) {
                return (int) $document['orden'];
            }
        }

        if (isset($documents[0]['orden'])) {
            return (int) $documents[0]['orden'];
        }

        return null;
    }

    private function repository(): DocumentosRepository
    {
        $this->repository ??= new DocumentosRepository();
        return $this->repository;
    }
}
