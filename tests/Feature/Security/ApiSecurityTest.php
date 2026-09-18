<?php

use App\Models\Insured;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

function fakeSecurityCountriesPayload(): array
{
    return [
        ['name' => ['common' => 'Spain'], 'translations' => ['spa' => ['common' => 'España']], 'cca2' => 'ES', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'flag' => '🇪🇸'],
        ['name' => ['common' => 'Ecuador'], 'translations' => ['spa' => ['common' => 'Ecuador']], 'cca2' => 'EC', 'region' => 'Americas', 'subregion' => 'South America', 'flag' => '🇪🇨'],
    ];
}

function validSecurityQuotePayload(array $overrides = []): array
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
        'raw.githubusercontent.com/*' => Http::response(fakeSecurityCountriesPayload()),
    ]);
});

it('throttles the api after the configured request limit', function () {
    RateLimiter::clear('api'.request()->ip() ?? '');

    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/countries')->assertOk();
    }

    $this->getJson('/api/countries')->assertStatus(429);
});

it('ignores server-controlled fields spoofed in the quote creation payload', function () {
    $response = $this->postJson('/api/quotes', validSecurityQuotePayload([
        'id' => 999999,
        'insured_id' => 999999,
        'status' => 'contracted',
        'reference' => 'SEG-2020-000001',
        'total' => 0.01,
        'subtotal' => 0.01,
        'surcharge_amount' => 0,
        'contracted_at' => now()->toIso8601String(),
    ]));

    $response->assertCreated()
        ->assertJsonPath('data.status', 'quoted')
        ->assertJsonPath('data.pricing.total', 39.6)
        ->assertJsonPath('data.contracted_at', null);

    expect($response->json('data.reference'))->not->toBe('SEG-2020-000001');
});

it('never resolves a quote by its numeric id through the public route', function () {
    $quote = Quote::factory()->for(Insured::factory())->create();

    $this->getJson("/api/quotes/{$quote->id}")->assertNotFound();
});

it('rejects an invalid document type instead of falling through to a default', function () {
    $this->postJson('/api/quotes', validSecurityQuotePayload(['document_type' => "cedula' OR '1'='1"]))
        ->assertJsonValidationErrors('document_type');
});

it('treats sql metacharacters in the admin search as literal text', function () {
    $this->actingAs(User::factory()->admin()->create());
    Quote::factory()->for(Insured::factory()->create(['first_name' => 'Gregorio']))->create();

    $response = $this->get('/admin/quotes?search='.urlencode("' OR '1'='1"));

    $response->assertOk()->assertSee('No se encontraron cotizaciones.');

    expect(Quote::count())->toBe(1);
});

it('treats sql metacharacters in the countries search as literal text', function () {
    $this->getJson('/api/countries?search='.urlencode("España' OR '1'='1"))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('escapes a stored xss payload when rendered in the admin listing', function () {
    $this->actingAs(User::factory()->admin()->create());
    $malicious = '<script>alert(1)</script>';

    Quote::factory()->for(Insured::factory()->create(['first_name' => $malicious]))->create();

    $response = $this->get('/admin/quotes');

    $response->assertOk()
        ->assertDontSee($malicious, false)
        ->assertSee(e($malicious), false);
});

it('escapes a stored xss payload when rendered in the pdf', function () {
    $malicious = '<script>alert(1)</script>';

    $quote = Quote::factory()->for(Insured::factory()->create(['first_name' => $malicious]))->create();

    $response = $this->get("/api/quotes/{$quote->reference}/pdf");

    $response->assertOk();

    expect($response->getContent())->not->toContain($malicious);
});

it('does not leak a stack trace or file paths in api error responses when debug is disabled', function () {
    config(['app.debug' => false]);

    $response = $this->getJson('/api/quotes/SEG-0000-000000');

    $response->assertNotFound();

    expect($response->json())->not->toHaveKey('trace')
        ->and($response->json())->not->toHaveKey('file')
        ->and($response->getContent())->not->toContain(base_path());
});

it('rolls back the insured update when quote creation fails inside the transaction', function () {
    $existing = Insured::factory()->create([
        'document_type' => 'cedula',
        'document_id' => '1710034065',
        'first_name' => 'Original',
    ]);

    Quote::creating(function () {
        throw new RuntimeException('simulated quote insert failure');
    });

    try {
        $this->postJson('/api/quotes', validSecurityQuotePayload(['first_name' => 'Tampered']));
    } catch (RuntimeException) {
        // Expected: the failure propagates out of DB::transaction() after rollback.
    }

    Quote::flushEventListeners();

    expect($existing->fresh()->first_name)->toBe('Original')
        ->and(Quote::count())->toBe(0);
});
