<?php
declare(strict_types=1);

use App\Modules\DatosIniciales\DatosInicialesController;

return [
    'GET_SUCURSAL_PRINCIPAL_DATA' => [DatosInicialesController::class, 'getPrincipalBranchData'],
];
