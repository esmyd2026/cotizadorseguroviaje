<?php

use Illuminate\Support\Facades\Http;

function fakeApiCountriesPayload(): array
{
    return [
        ['name' => ['common' => 'Spain'], 'translations' => ['spa' => ['common' => 'España']], 'cca2' => 'ES', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'flag' => '🇪🇸'],
        ['name' => ['common' => 'Estonia'], 'translations' => ['spa' => ['common' => 'Estonia']], 'cca2' => 'EE', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'flag' => '🇪🇪'],
    ];
}

it('lists all normalized countries', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response(fakeApiCountriesPayload()),
    ]);

    $this->getJson('/api/countries')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['name' => 'España', 'code' => 'ES', 'region' => 'Europe', 'dial_code' => '+34'])
        ->assertJsonStructure(['data' => [['dial_code', 'phone_length' => ['min', 'max']]]]);
});

it('filters countries by the search query parameter', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response(fakeApiCountriesPayload()),
    ]);

    $this->getJson('/api/countries?search=est')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['name' => 'Estonia']);
});

it('returns a service unavailable response when the provider fails', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response(status: 500),
    ]);

    $this->getJson('/api/countries')
        ->assertStatus(503)
        ->assertJsonStructure(['message']);
});
