<?php
declare(strict_types=1);

namespace App\Modules\Documentos\Repositories;

use App\Core\Database\BaseRepository;

class DocumentosRepository extends BaseRepository
{
    public function beginTransaction(): bool
    {
        return parent::beginTransaction();
    }

    public function commitTransaction(): bool
    {
        return parent::commit();
    }

    public function rollBackTransaction(): bool
    {
        return parent::rollBack();
    }

    public function getUserDocuments(int $userId, bool $validateBox): array
    {
        return $this->callProcedure('CALL getUserGenericDocuments(:idUser, :validarCaja)', [
            'idUser' => $userId,
            'validarCaja' => $validateBox ? 1 : 0,
        ]);
    }

    public function createDocumentByUser(int $userId): array
    {
        return $this->callProcedure('CALL crearNuevoDocumento(:usuario)', ['usuario' => $userId]);
    }

    public function createPurchaseDocumentByUser(int $userId, int $establishmentId): array
    {
        return $this->callProcedure('CALL crearNuevoDocumentoCompra(:usuario, :establecimiento)', [
            'usuario' => $userId,
            'establecimiento' => $establishmentId,
        ]);
    }

    public function changeActiveDocument(int $userId, int $documentId): array
    {
        return $this->callProcedure('CALL cambiarDocumentoActual(:usuario, :_documento)', [
            'usuario' => $userId,
            '_documento' => $documentId,
        ]);
    }

    public function changeActivePurchaseDocument(int $documentId): array
    {
        return $this->callProcedure('CALL cambiarDocumentoCompraActual(:_documento)', ['_documento' => $documentId]);
    }

    public function createExpenseDocumentByUser(int $userId): array
    {
        return $this->callProcedure('CALL crearNuevoDocumentoGasto(:usuario)', ['usuario' => $userId]);
    }

    public function createCreditAbonoByUser(int $userId, int $clientId): array
    {
        return $this->callProcedure('CALL crearNuevoDocumentoAbonoCxC(:usuario, :idCliente)', [
            'usuario' => $userId,
            'idCliente' => $clientId,
        ]);
    }

    public function createPayableCreditAbonoByUser(int $userId, int $clientId, int $establishmentId): array
    {
        return $this->callProcedure('CALL crearNuevoDocumentoAbonoCxP(:usuario, :idCliente, :establecimiento)', [
            'usuario' => $userId,
            'idCliente' => $clientId,
            'establecimiento' => $establishmentId,
        ]);
    }

    public function createDevolucionByUser(int $userId, int $documentId): array
    {
        return $this->callProcedure('CALL crearNuevoDocumentoDevolucion(:usuario, :idFactura)', [
            'usuario' => $userId,
            'idFactura' => $documentId,
        ]);
    }

    public function createNotaDebitoByUser(int $userId, string $documentCode): array
    {
        return $this->callProcedure('CALL crearNuevoDocumentoNotaDebito(:usuario, :idFactura)', [
            'usuario' => $userId,
            'idFactura' => $documentCode,
        ]);
    }

    public function returnProductFromDevolucionLine(int $lineId, float $quantity): array
    {
        return $this->callProcedure('CALL sp_devolver_producto_devolucion(:idFactura, :_cnt)', [
            'idFactura' => $lineId,
            '_cnt' => $quantity,
        ]);
    }

    public function returnProductFromNotaDebitoLine(int $lineId, float $quantity): array
    {
        return $this->callProcedure('CALL sp_devolver_producto_nota_debito(:idFactura, :_cnt)', [
            'idFactura' => $lineId,
            '_cnt' => $quantity,
        ]);
    }

    public function closeDocumentPayments(int $documentId): array
    {
        return $this->callProcedure('CALL actualizaCierresPagos(:documento)', ['documento' => $documentId]);
    }

    public function updateBonosBalance(int $documentId): array
    {
        return $this->callProcedure('CALL sp_actualizar_saldo_bonos(:documento)', ['documento' => $documentId]);
    }

    public function generateDeliveryDocument(int $documentId): array
    {
        return $this->callProcedure('CALL generarDomicilio(:documento)', ['documento' => $documentId]);
    }

    public function cancelDocumentByUser(int $userId, int $documentId): array
    {
        return $this->callProcedure('CALL cancelarDocumento(:usuario, :documento)', [
            'usuario' => $userId,
            'documento' => $documentId,
        ]);
    }

    public function returnProductFromSaleLine(int $lineId): array
    {
        return $this->callProcedure('CALL sp_devolver_producto(:_id_linea_devolucion)', [
            '_id_linea_devolucion' => $lineId,
        ]);
    }

    public function returnProductFromQuotationLine(int $lineId): array
    {
        return $this->callProcedure('CALL sp_devolver_producto_cotizacion(:_id_linea_devolucion)', [
            '_id_linea_devolucion' => $lineId,
        ]);
    }

    public function createQuotationByUser(int $userId, int $documentId): array
    {
        return $this->callProcedure('CALL SP_CREAR_DOCUMENTO_COTIZACION(:usuario, :documento)', [
            'usuario' => $userId,
            'documento' => $documentId,
        ]);
    }

    public function getDocumentObjectRow(int $documentId): ?array
    {
        return $this->fetchOne('SELECT * FROM vw_obj_documentos WHERE orden = :documento LIMIT 1', [
            'documento' => $documentId,
        ]);
    }

    public function getDocumentViewByCode(string $documentCode): ?array
    {
        return $this->fetchOne('SELECT * FROM vw_documentos WHERE idDocumentoFinal = :documento LIMIT 1', [
            'documento' => $documentCode,
        ]);
    }

    public function getCreditMovementByInvoice(int $invoiceId): ?array
    {
        return $this->fetchOne('SELECT * FROM mst_mov_credito WHERE idFacturaVenta = :factura LIMIT 1', [
            'factura' => $invoiceId,
        ]);
    }

    public function getDocumentLine(int $lineId): ?array
    {
        return $this->fetchOne('SELECT * FROM documentos_listado_productos WHERE id = :linea LIMIT 1', [
            'linea' => $lineId,
        ]);
    }

    public function insertDocumentLine(array $line): int
    {
        $this->execute(
            'INSERT INTO documentos_listado_productos (
                idDocBase, orden, idDocumento, idProducto, nombreProducto, presioVenta,
                porcent_iva, presioSinIVa, IVA, id_externo_auxiliar, cantidadVendida,
                valorTotal, usuario, cant_real_descontada, id_existencia, estado_linea_venta
            ) VALUES (
                :idDocBase, :orden, :idDocumento, :idProducto, :nombreProducto, :presioVenta,
                :porcent_iva, :presioSinIVa, :IVA, :id_externo_auxiliar, :cantidadVendida,
                :valorTotal, :usuario, :cant_real_descontada, :id_existencia, :estado_linea_venta
            )',
            [
                'idDocBase' => $line['idDocBase'] ?? null,
                'orden' => $line['orden'],
                'idDocumento' => $line['idDocumento'],
                'idProducto' => $line['idProducto'],
                'nombreProducto' => $line['nombreProducto'],
                'presioVenta' => $line['presioVenta'],
                'porcent_iva' => $line['porcent_iva'],
                'presioSinIVa' => $line['presioSinIVa'],
                'IVA' => $line['IVA'],
                'id_externo_auxiliar' => $line['id_externo_auxiliar'] ?? null,
                'cantidadVendida' => $line['cantidadVendida'],
                'valorTotal' => $line['valorTotal'],
                'usuario' => $line['usuario'],
                'cant_real_descontada' => $line['cant_real_descontada'],
                'id_existencia' => $line['id_existencia'],
                'estado_linea_venta' => $line['estado_linea_venta'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateDocumentTypeByName(int $documentId, string $typeName, bool $touchDateTime = false): bool
    {
        $sql = 'UPDATE documentos SET documento_odoo = 0, tipoDocumentoFinal = (SELECT id FROM tipos_de_documentos WHERE nombre = :tipo)';
        if ($touchDateTime) {
            $sql .= ', fecha = NOW(), hora = NOW()';
        }
        $sql .= ' WHERE orden = :documento';

        return $this->execute($sql, ['tipo' => $typeName, 'documento' => $documentId]);
    }

    public function updateDocumentCounterByName(int $documentId, string $counterName, bool $touchDateTime = false): bool
    {
        $sql = 'UPDATE documentos SET tipoDocumentoFinal = getIdContadorByName(:contador)';
        if ($touchDateTime) {
            $sql .= ', fecha = NOW(), hora = NOW()';
        }
        $sql .= ' WHERE orden = :documento';

        return $this->execute($sql, ['contador' => $counterName, 'documento' => $documentId]);
    }

    public function updateExpenseDocumentMetadata(int $documentId, int $clientId, float $value): bool
    {
        return $this->execute(
            'UPDATE documentos
             SET tipoDocumentoFinal = getIdContadorByName(:contador),
                 cliente = :cliente,
                 id_cliente = :cliente,
                 campo_auxiliar_1 = :valor
             WHERE orden = :documento',
            [
                'contador' => 'gastos',
                'cliente' => $clientId,
                'valor' => $value * -1,
                'documento' => $documentId,
            ]
        );
    }

    public function updateDocumentToDelivery(int $documentId): bool
    {
        return $this->execute(
            "UPDATE documentos SET tipoDocumentoFinal = getIdTipoDocumentoPorNombre('domicilio') WHERE orden = :documento",
            ['documento' => $documentId]
        );
    }

    public function changeDocumentBox(int $boxId, int $userId, int $documentId): bool
    {
        return $this->execute(
            'UPDATE documentos SET caja = :caja, usuario = :usuario WHERE orden = :documento',
            ['caja' => $boxId, 'usuario' => $userId, 'documento' => $documentId]
        );
    }

    public function insertAccountingOperation(int $userId, string $documentCode, string $documentName, string $documentDescription, int $documentOrder, int $clientId): int
    {
        $this->execute(
            "INSERT INTO cnt_operaciones (
                usuario, fechaOperacion, fechaCreacion, nombre, descripcion, idDocumento, idPersona
            ) VALUES (
                :usuario, NOW(), NOW(),
                CONCAT('Opr. auto. POS - Mov. Cnt. Nota Debito Pagos','-Doc => ', :codigo),
                CONCAT('Documento creado desde Nota Debito : ', :nombre, '-', :descripcion, '- Compra => ', :codigo),
                :documento, :persona
            )",
            [
                'usuario' => $userId,
                'codigo' => $documentCode,
                'nombre' => $documentName,
                'descripcion' => $documentDescription,
                'documento' => $documentOrder,
                'persona' => $clientId,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function insertCreditMovementAbono(int $creditMovementId, int $userId, float $totalAbonos, int $comprobante): bool
    {
        return $this->execute(
            'INSERT INTO mst_mov_credito_abonos (id_cartera, usuario_creacion, totalAbonos, comprobante, origen)
             VALUES (:cartera, :usuario, :total, :comprobante, :origen)',
            [
                'cartera' => $creditMovementId,
                'usuario' => $userId,
                'total' => $totalAbonos,
                'comprobante' => $comprobante,
                'origen' => 'devolucion',
            ]
        );
    }

    public function insertDebitCreditTransaction(int $accountId, float $debit, float $credit, int $userId, int $operationId, string $origin, int $thirdPartyId): bool
    {
        return $this->execute(
            'INSERT INTO cnt_transacciones (
                id_cuenta, valor_debito, valor_credito, fecha_transaccion, relacion_tabla,
                usuario, fecha_ingreso, cod_comprobante, origen_comprobante, cod_tercero
            ) VALUES (
                :cuenta, :debito, :credito, NOW(), :tabla,
                :usuario, NOW(), :comprobante, :origen, :tercero
            )',
            [
                'cuenta' => $accountId,
                'debito' => $debit,
                'credito' => $credit,
                'tabla' => 'documentos',
                'usuario' => $userId,
                'comprobante' => $operationId,
                'origen' => $origin,
                'tercero' => $thirdPartyId,
            ]
        );
    }

    public function insertPaymentReturnTransactionFromMedium(int $mediumId, float $amount, int $fallbackAccountId, int $userId, int $operationId, int $thirdPartyId): bool
    {
        return $this->execute(
            "INSERT INTO cnt_transacciones (
                id_cuenta, valor_debito, valor_credito, fecha_transaccion, relacion_tabla,
                usuario, fecha_ingreso, cod_comprobante, origen_comprobante, cod_tercero
            )
            SELECT CASE cuentaContable WHEN 0 THEN :fallback ELSE cuentaContable END,
                   :debito, 0, NOW(), 'documentos',
                   :usuario, NOW(), :comprobante, 'Nota Debito', :tercero
            FROM vw_medios
            WHERE id = :medio",
            [
                'fallback' => $fallbackAccountId,
                'debito' => $amount,
                'usuario' => $userId,
                'comprobante' => $operationId,
                'tercero' => $thirdPartyId,
                'medio' => $mediumId,
            ]
        );
    }
}
