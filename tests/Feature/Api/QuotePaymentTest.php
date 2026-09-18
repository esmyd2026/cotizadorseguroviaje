<?php

namespace Tests\Feature\Api;

use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use App\Models\Insured;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_payment_contracts_quote_and_creates_customer_account(): void
    {
        Notification::fake();
        $quote = Quote::factory()->for(Insured::factory())->create();

        $response = $this->postJson(
            "/api/quotes/{$quote->reference}/payment",
            $this->validPaymentPayload($quote),
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'contracted')
            ->assertJsonPath('data.payment.status', 'approved')
            ->assertJsonPath('data.payment.card_brand', 'visa')
            ->assertJsonPath('data.payment.card_last_four', '4242');

        $this->assertEquals((float) $quote->total, $response->json('data.payment.amount'));

        $customer = User::query()->where('insured_id', $quote->insured_id)->first();

        $this->assertNotNull($customer);
        $this->assertSame(UserRole::Customer, $customer->role);
        $this->assertSame(QuoteStatus::Contracted, $quote->fresh()->status);
        $this->assertNotNull($quote->fresh()->contracted_at);
        $this->assertSame($quote->insured->document_id, $customer->username);
        $this->assertTrue(Hash::check($quote->insured->document_id, $customer->password));
        $this->assertSame($quote->insured->document_id, $response->json('data.account.password'));
        $this->assertSame($customer->username, $response->json('data.account.username'));

        $payment = Payment::query()->firstOrFail();

        $this->assertArrayNotHasKey('card_number', $payment->getAttributes());
        $this->assertArrayNotHasKey('security_code', $payment->getAttributes());
    }

    public function test_declined_payment_is_recorded_without_contracting_or_creating_an_account(): void
    {
        Notification::fake();
        $quote = Quote::factory()->for(Insured::factory())->create();

        $response = $this->postJson(
            "/api/quotes/{$quote->reference}/payment",
            $this->validPaymentPayload($quote, ['card_number' => '4000 0000 0000 0002']),
        );

        $response->assertStatus(402)
            ->assertJsonPath('payment.status', 'declined')
            ->assertJsonPath('payment.failure_code', 'card_declined');

        $this->assertSame(QuoteStatus::Quoted, $quote->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'quote_id' => $quote->id,
            'status' => PaymentStatus::Declined->value,
            'card_last_four' => '0002',
        ]);
        $this->assertDatabaseMissing('users', ['insured_id' => $quote->insured_id]);
        Notification::assertNothingSent();
    }

    public function test_same_idempotency_key_never_creates_a_second_charge(): void
    {
        Notification::fake();
        $quote = Quote::factory()->for(Insured::factory())->create();
        $payload = $this->validPaymentPayload($quote);

        $firstResponse = $this->postJson("/api/quotes/{$quote->reference}/payment", $payload)->assertOk();
        $secondResponse = $this->postJson("/api/quotes/{$quote->reference}/payment", $payload)->assertOk();

        $this->assertSame(
            $firstResponse->json('data.payment.reference'),
            $secondResponse->json('data.payment.reference'),
        );
        $this->assertSame(1, Payment::query()->count());
        $this->assertSame(1, User::query()->count());
    }

    public function test_a_contracted_quote_rejects_a_different_payment_attempt(): void
    {
        Notification::fake();
        $quote = Quote::factory()->for(Insured::factory())->create();

        $this->postJson("/api/quotes/{$quote->reference}/payment", $this->validPaymentPayload($quote))->assertOk();

        $this->postJson(
            "/api/quotes/{$quote->reference}/payment",
            $this->validPaymentPayload($quote, ['idempotency_key' => (string) Str::uuid()]),
        )->assertConflict();

        $this->assertSame(1, Payment::query()->count());
    }

    public function test_payment_validates_card_expiration_security_code_email_and_terms(): void
    {
        $quote = Quote::factory()->for(Insured::factory())->create();

        $response = $this->postJson("/api/quotes/{$quote->reference}/payment", $this->validPaymentPayload($quote, [
            'card_holder' => '1',
            'card_number' => '1234 5678 9012 3456',
            'expiration_month' => 0,
            'expiration_year' => now()->subYear()->year,
            'security_code' => '12',
            'billing_email' => 'different@example.com',
            'terms' => false,
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'card_holder',
                'card_number',
                'expiration_month',
                'expiration_year',
                'security_code',
                'billing_email',
                'terms',
            ]);

        $this->assertSame(0, Payment::query()->count());
    }

    public function test_payment_rejects_an_expired_month_in_the_current_year(): void
    {
        $this->travelTo(now()->startOfYear()->addMonths(5));
        $quote = Quote::factory()->for(Insured::factory())->create();

        $this->postJson("/api/quotes/{$quote->reference}/payment", $this->validPaymentPayload($quote, [
            'expiration_month' => 5,
            'expiration_year' => now()->year,
        ]))->assertJsonValidationErrors('expiration_month');
    }

    private function validPaymentPayload(Quote $quote, array $overrides = []): array
    {
        return array_merge([
            'idempotency_key' => (string) Str::uuid(),
            'card_holder' => 'Gregorio Osorio',
            'card_number' => '4242 4242 4242 4242',
            'expiration_month' => now()->addYear()->month,
            'expiration_year' => now()->addYear()->year,
            'security_code' => '123',
            'billing_email' => $quote->insured->email,
            'terms' => true,
        ], $overrides);
    }
}
