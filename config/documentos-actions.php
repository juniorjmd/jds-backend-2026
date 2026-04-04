<?php
declare(strict_types=1);

use App\Modules\Documentos\DocumentosController;

return [
    'LISTAR_DOCUMENTOS' => [DocumentosController::class, 'listDocuments'],
    'SUBIR_DOCUMENTO' => [DocumentosController::class, 'uploadDocument'],
    'DESCARGAR_DOCUMENTO' => [DocumentosController::class, 'downloadDocument'],
    'BORRAR_DOCUMENTO' => [DocumentosController::class, 'deleteDocument'],
];
