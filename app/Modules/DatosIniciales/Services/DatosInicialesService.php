<?php
declare(strict_types=1);

namespace App\Modules\DatosIniciales\Services;

use App\Modules\DatosIniciales\Repositories\DatosInicialesRepository;

class DatosInicialesService
{
    public function __construct(
        private ?DatosInicialesRepository $repository = null
    ) {
        $this->repository ??= new DatosInicialesRepository();
    }

    public function getPrincipalBranchData(): array
    {
        $description = sha1('JDS_SUCURSAL_PRINCIPAL');
        $branches = $this->repository->findPrincipalBranchByDescription($description);

        if ($branches === []) {
            throw new \Exception('Error de datos, No existen valores iniciales para consultar');
        }

        return $branches;
    }
}
