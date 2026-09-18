<?php

use App\Models\Insured;
use App\Models\Quote;
use Illuminate\Support\Facades\Http;

function fakeQuoteCountriesPayload(): array
{
    return [
        ['name' => ['common' => 'Spain'], 'translations' => ['spa' => ['common' => 'España']], 'cca2' => 'ES', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'flag' => '🇪🇸'],
        ['name' => ['common' => 'Peru'], 'translations' => ['spa' => ['common' => 'Perú']], 'cca2' => 'PE', 'region' => 'Americas', 'subregion' => 'South America', 'flag' => '🇵🇪'],
        ['name' => ['common' => 'Ecuador'], 'translations' => ['spa' => ['common' => 'Ecuador']], 'cca2' => 'EC', 'region' => 'Americas', 'subregion' => 'South America', 'flag' => '🇪🇨'],
        ['name' => ['common' => 'United States'], 'translations' => ['spa' => ['common' => 'Estados Unidos']], 'cca2' => 'US', 'region' => 'Americas', 'subregion' => 'North America', 'flag' => '🇺🇸'],
    ];
}

function validQuotePayload(array $overrides = []): array
{
    return array_merge([
        'destination_country_code' => 'ES',
        'departure_date' => now()->addWeek()->toDateString(),
        'return_date' => now()->addWeek()->addDays(10)->toDateString(),
        'first_name' => 'Gregorio',
        'last_name' => 'Osorio',
        'document_type' => 'cedula',
        'document_id' => '1710034065',
        'email' => 'gregorio@example.com',
        'phone_country_code' => 'EC',
        'phone_number' => '987654321',
        'birth_date' => now()->subYears(30)->toDateString(),
    ], $overrides);
}

beforeEach(function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response(fakeQuoteCountriesPayload()),
    ]);
});

it('creates an insured and a quote with the calculated total', function () {
    $response = $this->postJson('/api/quotes', validQuotePayload());

    $response->assertCreated()
        ->assertJsonPath('data.status', 'quoted')
        ->assertJsonPath('data.destination.country_name', 'España')
        ->assertJsonPath('data.destination.region', 'Europe')
        ->assertJsonPath('data.trip.days', 11)
        ->assertJsonPath('data.pricing.total', 39.6);

    expect(Insured::where('document_id', '1710034065')->exists())->toBeTrue()
        ->and(Quote::where('reference', $response->json('data.reference'))->exists())->toBeTrue();

    $insured = Insured::where('document_id', '1710034065')->firstOrFail();

    expect($insured->first_name)->toBe('GREGORIO')
        ->and($insured->last_name)->toBe('OSORIO')
        ->and($insured->email)->toBe('GREGORIO@EXAMPLE.COM');
});

it('creates a multi-destination quote using the highest regional surcharge', function () {
    $response = $this->postJson('/api/quotes', validQuotePayload([
        'trip_type' => 'multiple',
        'destination_country_codes' => ['PE', 'US'],
    ]));

    $response->assertCreated()
        ->assertJsonPath('data.trip_type', 'multiple')
        ->assertJsonCount(2, 'data.destinations')
        ->assertJsonPath('data.destination.region', 'North America')
        ->assertJsonPath('data.pricing.surcharge_percentage', 15)
        ->assertJsonPath('data.pricing.total', 37.95);

    expect(Quote::first()->destinations)->toHaveCount(2);
});

it('requires at least two countries for a multi-destination trip', function () {
    $this->postJson('/api/quotes', validQuotePayload([
        'trip_type' => 'multiple',
        'destination_country_codes' => ['PE'],
    ]))->assertJsonValidationErrors('destination_country_codes');
});

it('allows only one country for a direct trip', function () {
    $this->postJson('/api/quotes', validQuotePayload([
        'trip_type' => 'direct',
        'destination_country_codes' => ['PE', 'US'],
    ]))->assertJsonValidationErrors('destination_country_codes');
});

it('reuses the existing insured record for a matching document id', function () {
    $this->postJson('/api/quotes', validQuotePayload())->assertCreated();
    $this->postJson('/api/quotes', validQuotePayload(['first_name' => 'Greg']))->assertCreated();

    expect(Insured::where('document_id', '1710034065')->count())->toBe(1)
        ->and(Quote::count())->toBe(2);
});

it('rejects a quote when the return date precedes the departure date', function () {
    $this->postJson('/api/quotes', validQuotePayload([
        'departure_date' => now()->addWeek()->toDateString(),
        'return_date' => now()->addDay()->toDateString(),
    ]))->assertJsonValidationErrors('return_date');
});

it('rejects a quote with a departure date in the past', function () {
    $this->postJson('/api/quotes', validQuotePayload([
        'departure_date' => now()->subDay()->toDateString(),
        'return_date' => now()->addDays(5)->toDateString(),
    ]))->assertJsonValidationErrors('departure_date');
});

it('rejects a quote with an invalid email address', function () {
    $this->postJson('/api/quotes', validQuotePayload(['email' => 'not-an-email']))
        ->assertJsonValidationErrors('email');
});

it('rejects a quote with a future birth date', function () {
    $this->postJson('/api/quotes', validQuotePayload(['birth_date' => now()->addDay()->toDateString()]))
        ->assertJsonValidationErrors('birth_date');
});

it('rejects a quote for a traveler under 18 years old', function () {
    $this->postJson('/api/quotes', validQuotePayload(['birth_date' => now()->subYears(17)->toDateString()]))
        ->assertJsonValidationErrors('birth_date');
});

it('rejects an invalid Ecuadorian identity card', function () {
    $this->postJson('/api/quotes', validQuotePayload(['document_id' => '1710034064']))
        ->assertJsonValidationErrors('document_id');
});

it('accepts an alphanumeric passport', function () {
    $this->postJson('/api/quotes', validQuotePayload([
        'document_type' => 'passport',
        'document_id' => 'AB123456',
    ]))->assertCreated();
});

it('rejects a quote missing required traveler fields', function () {
    $this->postJson('/api/quotes', validQuotePayload(['first_name' => '', 'document_id' => '']))
        ->assertJsonValidationErrors(['first_name', 'document_id']);
});

it('rejects a quote for an unknown destination country code', function () {
    $this->postJson('/api/quotes', validQuotePayload(['destination_country_code' => 'ZZ']))
        ->assertJsonValidationErrors('destination_country_codes.0');
});

it('rejects a phone number that is too short for the selected country', function () {
    $this->postJson('/api/quotes', validQuotePayload(['phone_number' => '123']))
        ->assertJsonValidationErrors('phone_number');
});

it('accepts a valid phone number for a different country', function () {
    $response = $this->postJson('/api/quotes', validQuotePayload([
        'phone_country_code' => 'US',
        'phone_number' => '2025550123',
    ]));

    $response->assertCreated();

    expect(Insured::where('document_id', '1710034065')->first()->phone)->toBe('+12025550123');
});

it('shows a quote by its reference', function () {
    $quote = Quote::factory()->for(Insured::factory())->create();

    $this->getJson("/api/quotes/{$quote->reference}")
        ->assertOk()
        ->assertJsonPath('data.reference', $quote->reference);
});
