<?php

use App\Enums\QuoteStatus;
use App\Models\Insured;
use App\Models\Quote;

it('downloads a pdf for a quoted quote', function () {
    $quote = Quote::factory()->for(Insured::factory())->create(['status' => QuoteStatus::Quoted]);

    $response = $this->get("/api/quotes/{$quote->reference}/pdf");

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload("cotizacion-{$quote->reference}.pdf");

    expect($response->getContent())->toStartWith('%PDF');
});

it('downloads a pdf for a contracted quote', function () {
    $quote = Quote::factory()->for(Insured::factory())->contracted()->create();

    $this->get("/api/quotes/{$quote->reference}/pdf")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('returns not found for an unknown quote reference', function () {
    $this->get('/api/quotes/SEG-2026-999999/pdf')->assertNotFound();
});
