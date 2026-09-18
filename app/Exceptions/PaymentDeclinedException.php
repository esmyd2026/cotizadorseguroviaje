<?php

namespace App\Exceptions;

use App\Models\Payment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentDeclinedException extends Exception
{
    public function __construct(public readonly Payment $payment)
    {
        parent::__construct(match ($payment->failure_code) {
            'insufficient_funds' => 'La tarjeta no tiene fondos suficientes para completar el pago simulado.',
            default => 'El pago simulado fue rechazado. Revisa los datos o utiliza otra tarjeta.',
        });
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'payment' => [
                'status' => $this->payment->status->value,
                'reference' => $this->payment->reference,
                'failure_code' => $this->payment->failure_code,
            ],
        ], 402);
    }
}
