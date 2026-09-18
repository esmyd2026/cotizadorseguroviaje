<?php

namespace App\Services;

use App\Enums\Region;
use App\Exceptions\CountriesUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CountriesService
{
    private const CACHE_KEY = 'countries.all';

    private const CACHE_TTL_MINUTES = 1440;

    /**
     * REST Countries' free v3.1 API was retired in favor of a paid, API-key-gated v5.
     * This dataset is the same source data REST Countries itself was built from,
     * published as an openly licensed, unauthenticated static JSON file.
     */
    private const ENDPOINT = 'https://raw.githubusercontent.com/mledoze/countries/master/dist/countries.json';

    public function search(string $query): Collection
    {
        $query = trim($query);

        if ($query === '') {
            return collect();
        }

        $needle = Str::lower($query);

        return $this->all()
            ->filter(fn (array $country) => str_contains(Str::lower($country['name']), $needle))
            ->values();
    }

    public function findByCode(string $code): ?array
    {
        $code = strtoupper($code);

        return $this->all()->firstWhere('code', $code);
    }

    public function all(): Collection
    {
        // Cache stores never unserialize plain PHP objects (see config/cache.php),
        // so the list is persisted as a plain array and re-wrapped on read.
        return collect(Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => $this->fetch()->all(),
        ));
    }

    private function fetch(): Collection
    {
        try {
            $response = Http::timeout(5)
                ->retry(2, 200, throw: false)
                ->get(self::ENDPOINT);
        } catch (ConnectionException $exception) {
            Log::warning('REST Countries connection failed.', ['message' => $exception->getMessage()]);

            throw new CountriesUnavailableException($exception);
        }

        if ($response->failed()) {
            Log::warning('REST Countries returned an error response.', ['status' => $response->status()]);

            throw new CountriesUnavailableException;
        }

        $countries = $response->json();

        if (! is_array($countries)) {
            Log::warning('REST Countries returned an unexpected payload shape.');

            throw new CountriesUnavailableException;
        }

        return collect($countries)
            ->map(fn (array $country) => $this->normalize($country))
            ->filter()
            ->sortBy('name')
            ->values();
    }

    private function normalize(array $country): ?array
    {
        // The UI is Spanish-facing, so prefer the Spanish translation over the
        // dataset's default English common name (e.g. "España", not "Spain").
        $name = $country['translations']['spa']['common'] ?? $country['name']['common'] ?? null;
        $code = $country['cca2'] ?? null;
        $region = $this->resolveRegion($country['region'] ?? null, $country['subregion'] ?? null);

        if (! $name || ! $code || ! $region) {
            return null;
        }

        return [
            'name' => $name,
            'code' => $code,
            'flag' => $country['flag'] ?? '',
            'region' => $region->value,
        ];
    }

    private function resolveRegion(?string $region, ?string $subregion): ?Region
    {
        return match (true) {
            $region === 'Africa' => Region::Africa,
            $region === 'Asia' => Region::Asia,
            $region === 'Europe' => Region::Europe,
            $region === 'Oceania' => Region::Oceania,
            $region === 'Americas' && $subregion === 'South America' => Region::SouthAmerica,
            $region === 'Americas' => Region::NorthAmerica,
            default => null,
        };
    }
}
