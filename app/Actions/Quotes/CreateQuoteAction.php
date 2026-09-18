<?php

namespace App\Actions\Quotes;

use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Models\Insured;
use App\Models\Quote;
use App\Services\CountriesService;
use App\Services\PhoneNumberService;
use App\Services\QuoteCalculatorService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateQuoteAction
{
    public function __construct(
        private readonly CountriesService $countries,
        private readonly QuoteCalculatorService $calculator,
        private readonly PhoneNumberService $phoneNumbers,
    ) {}

    /**
     * @param  array{
     *     trip_type: string,
     *     destination_country_codes: array<int, string>,
     *     departure_date: string,
     *     return_date: string,
     *     first_name: string,
     *     last_name: string,
     *     document_type: string,
     *     document_id: string,
     *     email: string,
     *     phone_country_code: string,
     *     phone_number: string,
     *     birth_date: string,
     * }  $data
     */
    public function execute(array $data): Quote
    {
        $destinations = collect($data['destination_country_codes'])
            ->map(fn (string $code) => $this->countries->findByCode($code))
            ->filter()
            ->values();
        $primaryDestination = $destinations->first();
        $highestRiskDestination = $destinations->sortByDesc(
            fn (array $destination) => Region::from($destination['region'])->surchargePercentage(),
        )->first();

        $calculation = $this->calculator->calculate(
            Carbon::parse($data['departure_date']),
            Carbon::parse($data['return_date']),
            Region::from($highestRiskDestination['region']),
        );

        return DB::transaction(function () use ($data, $destinations, $primaryDestination, $calculation) {
            $insured = Insured::updateOrCreate(
                [
                    'document_type' => $data['document_type'],
                    'document_id' => $data['document_id'],
                ],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $this->phoneNumbers->toE164($data['phone_number'], $data['phone_country_code']),
                    'birth_date' => $data['birth_date'],
                ],
            );

            $quote = $insured->quotes()->create([
                ...$calculation,
                'reference' => (string) Str::uuid(),
                'trip_type' => $data['trip_type'],
                'destination_country_code' => $primaryDestination['code'],
                'destination_country_name' => $destinations->pluck('name')->join(', '),
                'destinations' => $destinations->all(),
                'departure_date' => $data['departure_date'],
                'return_date' => $data['return_date'],
                'status' => QuoteStatus::Quoted,
            ]);

            // The reference is human-facing (e.g. "SEG-2026-000124"); the id is only
            // known once the row exists, so the placeholder above is replaced here.
            $quote->update([
                'reference' => sprintf('SEG-%d-%06d', now()->year, $quote->id),
            ]);

            return $quote;
        });
    }
}
