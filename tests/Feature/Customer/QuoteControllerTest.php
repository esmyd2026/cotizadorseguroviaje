<?php

use App\Models\Insured;
use App\Models\Quote;
use App\Models\User;

it('redirects a guest to login', function () {
    $this->get(route('customer.quotes.index'))->assertRedirect(route('login'));
});

it('shows a customer only their own quotes', function () {
    $mine = Insured::factory()->create();
    $customer = User::factory()->create(['insured_id' => $mine->id]);
    $myQuote = Quote::factory()->for($mine)->create(['reference' => 'SEG-2026-000100']);

    $otherInsured = Insured::factory()->create();
    Quote::factory()->for($otherInsured)->create(['reference' => 'SEG-2026-000200']);

    $this->actingAs($customer)
        ->get(route('customer.quotes.index'))
        ->assertOk()
        ->assertSee('SEG-2026-000100')
        ->assertDontSee('SEG-2026-000200');
});

it('shows an empty state when the customer has no quotes yet', function () {
    $insured = Insured::factory()->create();
    $customer = User::factory()->create(['insured_id' => $insured->id]);

    $this->actingAs($customer)
        ->get(route('customer.quotes.index'))
        ->assertOk()
        ->assertSee('Todavía no tienes cotizaciones');
});

it('sends an admin to the admin panel instead', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('customer.quotes.index'))
        ->assertRedirect(route('admin.quotes.index'));
});

it('embeds the full quote detail as JSON for the modal', function () {
    $insured = Insured::factory()->create();
    $customer = User::factory()->create(['insured_id' => $insured->id]);
    $quote = Quote::factory()->for($insured)->contracted()->create(['reference' => 'SEG-2026-000300']);

    $response = $this->actingAs($customer)->get(route('customer.quotes.index'))->assertOk();

    $response->assertSee('data-quote-detail', false);
    $response->assertSee($quote->reference);
});
