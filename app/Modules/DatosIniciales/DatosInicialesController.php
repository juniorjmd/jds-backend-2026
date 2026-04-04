<?php
declare(strict_types=1);

namespace App\Modules\DatosIniciales;

use App\Core\Http\Response;
use App\Modules\DatosIniciales\Services\DatosInicialesService;

class DatosInicialesController
{
    public function __construct(
        private ?DatosInicialesService $service = null
    ) {
        $this->service ??= new DatosInicialesService();
    }

    public function getPrincipalBranchData(): void
    {
        try {
            (new Response())
                ->status(200)
                ->json($this->service->getPrincipalBranchData())
                ->send();
        } catch (\Throwable $e) {
            (new Response())
                ->status(500)
                ->json(['error' => $e->getMessage()])
                ->send();
        }
    }
}
