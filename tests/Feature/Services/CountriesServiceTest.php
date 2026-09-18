<?php

use App\Exceptions\CountriesUnavailableException;
use App\Services\CountriesService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

function fakeCountriesPayload(): array
{
    return [
        ['name' => ['common' => 'Spain'], 'translations' => ['spa' => ['common' => 'España']], 'cca2' => 'ES', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'flag' => '🇪🇸'],
        ['name' => ['common' => 'Estonia'], 'translations' => ['spa' => ['common' => 'Estonia']], 'cca2' => 'EE', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'flag' => '🇪🇪'],
        ['name' => ['common' => 'Peru'], 'translations' => ['spa' => ['common' => 'Perú']], 'cca2' => 'PE', 'region' => 'Americas', 'subregion' => 'South America', 'flag' => '🇵🇪'],
        ['name' => ['common' => 'United States'], 'translations' => ['spa' => ['common' => 'Estados Unidos']], 'cca2' => 'US', 'region' => 'Americas', 'subregion' => 'North America', 'flag' => '🇺🇸'],
        ['name' => ['common' => 'Antarctica'], 'translations' => ['spa' => ['common' => 'Antártida']], 'cca2' => 'AQ', 'region' => 'Antarctic', 'subregion' => '', 'flag' => ''],
    ];
}

it('normalizes countries and maps americas subregions to the correct region', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response(fakeCountriesPayload()),
    ]);

    $countries = app(CountriesService::class)->all();

    expect($countries)->toHaveCount(4)
        ->and($countries->firstWhere('code', 'PE')['region'])->toBe('South America')
        ->and($countries->firstWhere('code', 'US')['region'])->toBe('North America')
        ->and($countries->firstWhere('code', 'ES')['region'])->toBe('Europe')
        ->and($countries->firstWhere('code', 'AQ'))->toBeNull();
});

it('filters countries by a case-insensitive partial name match', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response(fakeCountriesPayload()),
    ]);

    $results = app(CountriesService::class)->search('paña');

    expect($results)->toHaveCount(1)
        ->and($results->first()['name'])->toBe('España');
});

it('caches the country list to avoid repeated external calls', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response(fakeCountriesPayload()),
    ]);

    $service = app(CountriesService::class);
    $service->all();
    $service->all();

    Http::assertSentCount(1);
});

it('throws when the countries provider returns an error response', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response(status: 500),
    ]);

    expect(fn () => app(CountriesService::class)->all())
        ->toThrow(CountriesUnavailableException::class);
});

it('throws when the countries provider cannot be reached', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => fn () => throw new ConnectionException('Connection timed out.'),
    ]);

    expect(fn () => app(CountriesService::class)->all())
        ->toThrow(CountriesUnavailableException::class);
});
