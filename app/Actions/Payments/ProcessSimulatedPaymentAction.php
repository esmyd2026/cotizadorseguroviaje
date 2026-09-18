<?php

namespace App\Actions\Payments;

use App\Actions\Users\ProvisionCustomerAccountAction;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Exceptions\PaymentDeclinedException;
use App\Exceptions\QuoteAlreadyContractedException;
use App\Models\Payment;
use App\Models\Quote;
use App\Services\CardValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessSimulatedPaymentAction
{
    public function __construct(
        private readonly CardValidationService $cards,
        private readonly ProvisionCustomerAccountAction $provisionCustomerAccount,
    ) {}

    /**
     * @param  array{
     *     idempotency_key: string,
     *     card_holder: string,
     *     card_number: string,
     *     expiration_month: int,
     *     expiration_year: int,
     *     security_code: string,
     *     billing_email: string,
     *     terms: bool|string,
     * }  $data
     */
    public function execute(Quote $quote, array $data): Payment
    {
        $payment = DB::transaction(function () use ($quote, $data): Payment {
            $lockedQuote = Quote::query()
                ->with('insured')
                ->whereKey($quote->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $existingPayment = $lockedQuote->payments()
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existingPayment) {
                return $existingPayment;
            }

            if ($lockedQuote->status === QuoteStatus::Contracted) {
                throw new QuoteAlreadyContractedException($lockedQuote);
            }

            $failureCode = $this->failureCodeFor($data['card_number']);
            $status = $failureCode === null ? PaymentStatus::Approved : PaymentStatus::Declined;
            $brand = $this->cards->brand($data['card_number']);

            $payment = $lockedQuote->payments()->create([
                'idempotency_key' => $data['idempotency_key'],
                'reference' => 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(10)),
                'status' => $status,
                'card_brand' => $brand,
                'card_last_four' => substr($data['card_number'], -4),
                'amount' => $lockedQuote->total,
                'currency' => 'USD',
                'authorization_code' => $status === PaymentStatus::Approved ? Str::upper(Str::random(8)) : null,
                'failure_code' => $failureCode,
                'paid_at' => $status === PaymentStatus::Approved ? now() : null,
            ]);

            if ($status === PaymentStatus::Approved) {
                $lockedQuote->update([
                    'status' => QuoteStatus::Contracted,
                    'contracted_at' => now(),
                ]);

                $this->provisionCustomerAccount->execute(
                    $lockedQuote->insured,
                    $lockedQuote->insured->email,
                    "{$lockedQuote->insured->first_name} {$lockedQuote->insured->last_name}",
                );
            }

            return $payment;
        });

        if ($payment->status === PaymentStatus::Declined) {
            throw new PaymentDeclinedException($payment);
        }

        return $payment;
    }

    private function failureCodeFor(string $cardNumber): ?string
    {
        return match ($cardNumber) {
            '4000000000000002' => 'card_declined',
            '4000000000009995' => 'insufficient_funds',
            default => null,
        };
    }
}
