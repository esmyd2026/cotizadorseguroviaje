<?php

use App\Enums\QuoteStatus;
use App\Models\Insured;
use App\Models\Quote;
use App\Models\User;

it('lists quotes with their insured data', function () {
    actingAsAdmin();

    $insured = Insured::factory()->create(['first_name' => 'Gregorio', 'last_name' => 'Osorio', 'document_id' => '99999999']);
    Quote::factory()->for($insured)->create(['reference' => 'SEG-2026-000001']);

    $this->get('/admin/quotes')
        ->assertOk()
        ->assertSee('SEG-2026-000001')
        ->assertSee('Gregorio Osorio')
        ->assertSee('99999999');
});

it('filters quotes by search term', function () {
    actingAsAdmin();

    Quote::factory()->for(Insured::factory()->create(['first_name' => 'Ana', 'last_name' => 'Perez']))
        ->create(['reference' => 'SEG-2026-000010', 'destination_country_name' => 'Spain']);
    Quote::factory()->for(Insured::factory()->create(['first_name' => 'Luis', 'last_name' => 'Gomez']))
        ->create(['reference' => 'SEG-2026-000020', 'destination_country_name' => 'Mexico']);

    $response = $this->get('/admin/quotes?search=Ana');

    $response->assertOk()
        ->assertSee('SEG-2026-000010')
        ->assertDontSee('SEG-2026-000020');
});

it('filters quotes by status', function () {
    actingAsAdmin();

    Quote::factory()->for(Insured::factory())->create(['reference' => 'SEG-2026-000030', 'status' => QuoteStatus::Quoted]);
    Quote::factory()->for(Insured::factory())->contracted()->create(['reference' => 'SEG-2026-000040']);

    $response = $this->get('/admin/quotes?status=contracted');

    $response->assertOk()
        ->assertSee('SEG-2026-000040')
        ->assertDontSee('SEG-2026-000030');
});

it('paginates the quote listing', function () {
    actingAsAdmin();

    Quote::factory()->for(Insured::factory())->count(20)->create();

    $this->get('/admin/quotes')
        ->assertOk()
        ->assertViewHas('quotes', fn ($quotes) => $quotes->count() === 15 && $quotes->total() === 20);
});

it('shows quote and revenue indicators', function () {
    actingAsAdmin();

    Quote::factory()->for(Insured::factory())->create(['total' => 30]);
    Quote::factory()->for(Insured::factory())->contracted()->create(['total' => 50]);

    $this->get('/admin/quotes')
        ->assertOk()
        ->assertViewHas('stats', function ($stats) {
            return $stats['total'] === 2
                && $stats['contracted'] === 1
                && $stats['revenue'] === 50.0
                && $stats['conversion_rate'] === 50.0;
        });
});

it('redirects a guest to the login page', function () {
    $this->get('/admin/quotes')->assertRedirect('/login');
});

it('forbids a customer account from accessing the admin panel', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/admin/quotes')->assertForbidden();
});
