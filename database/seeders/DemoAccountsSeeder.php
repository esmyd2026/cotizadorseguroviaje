<?php

namespace Database\Seeders;

use App\Actions\Users\ProvisionCustomerAccountAction;
use App\Enums\CardBrand;
use App\Enums\DocumentType;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Models\Insured;
use App\Models\Quote;
use App\Services\QuoteCalculatorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Guarantees one fixed, always-reproducible demo customer account exists —
 * unlike ContractsDemoSeeder's randomized batch (which skips on a second
 * run), this always upserts the same record so the credentials shown on the
 * login screen never go stale or drift after a reseed.
 */
class DemoAccountsSeeder extends Seeder
{
    public function run(QuoteCalculatorService $calculator, ProvisionCustomerAccountAction $provisionAccount): void
    {
        $documentId = config('demo.customer_document_id');

        $insured = Insured::updateOrCreate(
            ['document_type' => DocumentType::Cedula, 'document_id' => $documentId],
            [
                'first_name' => 'Cliente',
                'last_name' => 'Demo',
                'email' => 'cliente.demo@gestionsegura.test',
                'phone' => '+593987654321',
                'birth_date' => '1990-05-14',
            ],
        );

        $departureDate = Carbon::now()->addDays(21);
        $returnDate = (clone $departureDate)->addDays(10);
        $calculation = $calculator->calculate($departureDate, $returnDate, Region::SouthAmerica);

        $quote = Quote::updateOrCreate(
            ['reference' => 'SEG-DEMO-000001'],
            [
                ...$calculation,
                'insured_id' => $insured->id,
                'trip_type' => 'direct',
                'destination_country_code' => 'PE',
                'destination_country_name' => 'Peru',
                'destinations' => [['code' => 'PE', 'name' => 'Peru', 'region' => Region::SouthAmerica->value]],
                'departure_date' => $departureDate->toDateString(),
                'return_date' => $returnDate->toDateString(),
                'status' => QuoteStatus::Contracted,
                'contracted_at' => Carbon::now()->subDay(),
            ],
        );

        $quote->payments()->updateOrCreate(
            ['idempotency_key' => 'demo-seed-payment-'.$quote->reference],
            [
                'reference' => 'PAY-DEMO-00000001',
                'status' => PaymentStatus::Approved,
                'card_brand' => CardBrand::Visa,
                'card_last_four' => '4242',
                'amount' => $quote->total,
                'currency' => 'USD',
                'authorization_code' => 'DEMO0001',
                'failure_code' => null,
                'paid_at' => $quote->contracted_at,
            ],
        );

        $provisionAccount->execute($insured, $insured->email, "{$insured->first_name} {$insured->last_name}");

        // provisionAccount only sets the password on first creation (it never
        // resets a customer's own chosen password on repeat calls); force it
        // back to the documented demo value on every reseed so the login
        // screen's advertised credentials always work.
        $insured->user?->forceFill(['password' => $documentId])->save();

        $this->command?->info(
            'Cuenta de cliente demo: usuario y contraseña '.$documentId,
        );
    }
}
