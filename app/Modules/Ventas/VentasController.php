<?php
declare(strict_types=1);

namespace App\Modules\Ventas;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Ventas\Services\VentasService;

class VentasController
{
    private Request $request;
    private VentasService $service;

    public function __construct(Request $request, VentasService $service)
    {
        $this->request = $request;
        $this->service = $service;
    }

    public function assignPurchaseCreditPayments(): void
    {
        try {
            $result = $this->service->assignPurchaseCreditPayments(
                documentOrder: (int) $this->request->input('ordenDocumento', 0),
                payments: $this->normalizePayments($this->request->input('pagos', [])),
                installments: (int) $this->request->input('numCuotas', 1),
                installmentDays: (int) $this->request->input('numDiasCuotas', 30)
            );

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('ASSIGN_PURCHASE_CREDIT_PAYMENTS_ERROR', $e->getMessage());
        }
    }

    public function updatePurchaseCreditPayments(): void
    {
        try {
            $result = $this->service->updatePurchaseCreditPayments(
                documentOrder: (int) $this->request->input('ordenDocumento', 0),
                payments: $this->normalizePayments($this->request->input('pagos', [])),
                installments: (int) $this->request->input('numCuotas', 1),
                installmentDays: (int) $this->request->input('numDiasCuotas', 30),
                externalInvoice: (string) $this->request->input('facturaExterna', ''),
                supplierId: (int) $this->request->input('proveedor', 0),
                establishmentId: (int) $this->request->input('establecimiento', 0),
                date: (string) $this->request->input('fecha', '')
            );

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('UPDATE_PURCHASE_CREDIT_PAYMENTS_ERROR', $e->getMessage());
        }
    }

    public function assignSalesCreditPayments(): void
    {
        try {
            $result = $this->service->assignSalesCreditPayments(
                documentOrder: (int) $this->request->input('ordenDocumento', 0),
                payments: $this->normalizePayments($this->request->input('pagos', [])),
                installments: (int) $this->request->input('numCuotas', 1),
                installmentDays: (int) $this->request->input('numDiasCuotas', 30),
                remision: (bool) $this->request->input('remision', false)
            );

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('ASSIGN_SALES_CREDIT_PAYMENTS_ERROR', $e->getMessage());
        }
    }

    public function assignCreditInstallmentPayment(): void
    {
        try {
            $result = $this->service->assignCreditInstallmentPayment(
                documentOrder: (int) $this->request->input('ordenDocumento', 0),
                payments: $this->normalizePayments($this->request->input('pagos', []))
            );

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('ASSIGN_CREDIT_INSTALLMENT_PAYMENT_ERROR', $e->getMessage());
        }
    }

    private function normalizePayments(mixed $payments): array
    {
        if (is_string($payments)) {
            $decoded = json_decode($payments, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($payments) ? $payments : [];
    }
}
