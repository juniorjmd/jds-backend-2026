<?php
declare(strict_types=1);

use App\Modules\Documentos\DocumentosController;

return [
    'GET_DOCUMENTOS_USUARIO_ACTUAL' => [DocumentosController::class, 'getCurrentUserDocuments'],
    'GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA' => [DocumentosController::class, 'getCurrentUserDocumentsByActiveBox'],
    'CREAR_DOCUMENTO_POR_USUARIO' => [DocumentosController::class, 'createCurrentUserDocument'],
    'CREAR_DOCUMENTO_COMPRA_POR_USUARIO' => [DocumentosController::class, 'createCurrentUserPurchaseDocument'],
    'CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO' => [DocumentosController::class, 'changeCurrentUserDocument'],
    'CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO' => [DocumentosController::class, 'changeCurrentUserPurchaseDocument'],
    'CREAR_DOCUMENTO_GASTO_POR_USUARIO' => [DocumentosController::class, 'createExpenseDocument'],
    'ASIGNAR_ABONO_DOCUMENTOS_CREDITO' => [DocumentosController::class, 'createCreditAbonoDocument'],
    'ASIGNAR_ABONO_DOCUMENTOS_CREDITO_POR_PAGAR' => [DocumentosController::class, 'createPayableCreditAbonoDocument'],
    'GENERAR_DOCUMENTOS_DEVOLUCION' => [DocumentosController::class, 'createDevolucionDocument'],
    'GENERAR_DOCUMENTOS_NOTA_DEBITO' => [DocumentosController::class, 'createNotaDebitoDocument'],
    'CERRAR_DOCUMENTO_FACTURA' => [DocumentosController::class, 'closeInvoiceDocument'],
    'CERRAR_DOCUMENTO_REMISION' => [DocumentosController::class, 'closeRemisionDocument'],
    'CAMBIAR_DOCUMENTO_A_ENVIO' => [DocumentosController::class, 'sendDocumentToDelivery'],
    'CANCELAR_DOCUMENTO_POR_USUARIO' => [DocumentosController::class, 'cancelDocument'],
    'CREAR_DOCUMENTO_COTIZACION_POR_USUARIO' => [DocumentosController::class, 'convertDocumentToQuotation'],
    'CAMBIAR_DOCUMENTO_POR_CAJA' => [DocumentosController::class, 'changeDocumentBox'],
    'LISTAR_DOCUMENTOS' => [DocumentosController::class, 'listDocuments'],
    'SUBIR_DOCUMENTO' => [DocumentosController::class, 'uploadDocument'],
    'DESCARGAR_DOCUMENTO' => [DocumentosController::class, 'downloadDocument'],
    'BORRAR_DOCUMENTO' => [DocumentosController::class, 'deleteDocument'],
];
