<?php

namespace Database\Seeders;

use App\Actions\Users\ProvisionCustomerAccountAction;
use App\Enums\CardBrand;
use App\Enums\DocumentType;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Models\Insured;
use App\Services\QuoteCalculatorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Fills the "contrataciones" module with realistic demo data: quotes in both
 * the quoted and contracted states, spread across regions and dates, each
 * contracted one carrying its simulated payment and its auto-provisioned
 * customer account (username/password = the insured's document id).
 *
 * Safe to re-run: it skips seeding if demo records already exist.
 */
class ContractsDemoSeeder extends Seeder
{
    private const DEMO_MARKER = '+demo-seed';

    /**
     * @var array<string, array<int, array{code: string, name: string}>>
     */
    private const DESTINATIONS_BY_REGION = [
        'South America' => [
            ['code' => 'PE', 'name' => 'Peru'],
            ['code' => 'CO', 'name' => 'Colombia'],
            ['code' => 'AR', 'name' => 'Argentina'],
            ['code' => 'BR', 'name' => 'Brazil'],
            ['code' => 'CL', 'name' => 'Chile'],
        ],
        'North America' => [
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'CA', 'name' => 'Canada'],
            ['code' => 'MX', 'name' => 'Mexico'],
        ],
        'Europe' => [
            ['code' => 'ES', 'name' => 'Spain'],
            ['code' => 'FR', 'name' => 'France'],
            ['code' => 'IT', 'name' => 'Italy'],
            ['code' => 'DE', 'name' => 'Germany'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
        ],
        'Asia' => [
            ['code' => 'JP', 'name' => 'Japan'],
            ['code' => 'TH', 'name' => 'Thailand'],
            ['code' => 'AE', 'name' => 'United Arab Emirates'],
            ['code' => 'CN', 'name' => 'China'],
        ],
        'Africa' => [
            ['code' => 'EG', 'name' => 'Egypt'],
            ['code' => 'ZA', 'name' => 'South Africa'],
            ['code' => 'MA', 'name' => 'Morocco'],
        ],
        'Oceania' => [
            ['code' => 'AU', 'name' => 'Australia'],
            ['code' => 'NZ', 'name' => 'New Zealand'],
        ],
    ];

    public function run(QuoteCalculatorService $calculator, ProvisionCustomerAccountAction $provisionAccount): void
    {
        if (Insured::where('email', 'like', '%'.self::DEMO_MARKER.'%')->exists()) {
            $this->command?->info('ContractsDemoSeeder: los datos de ejemplo ya existen, se omite.');

            return;
        }

        $credentials = [];
        $regions = array_keys(self::DESTINATIONS_BY_REGION);
        $recordCount = 45;

        for ($i = 1; $i <= $recordCount; $i++) {
            $isMultiDestination = fake()->boolean(15);
            $destinationCount = $isMultiDestination ? fake()->numberBetween(2, 3) : 1;

            $destinations = [];
            for ($d = 0; $d < $destinationCount; $d++) {
                $region = fake()->randomElement($regions);
                $destination = fake()->randomElement(self::DESTINATIONS_BY_REGION[$region]);
                $destinations[] = [...$destination, 'region' => $region];
            }

            $highestRiskDestination = collect($destinations)
                ->sortByDesc(fn (array $destination) => Region::from($destination['region'])->surchargePercentage())
                ->first();

            $departureDate = Carbon::now()->addDays(fake()->numberBetween(-60, 150));
            $returnDate = (clone $departureDate)->addDays(fake()->numberBetween(4, 21));

            $calculation = $calculator->calculate($departureDate, $returnDate, Region::from($highestRiskDestination['region']));

            $isCedula = fake()->boolean(55);
            $documentType = $isCedula ? DocumentType::Cedula : DocumentType::Passport;
            $documentId = $isCedula ? $this->generateValidCedula() : Str::upper(fake()->unique()->bothify('??######'));

            $insured = Insured::factory()->create([
                'document_type' => $documentType,
                'document_id' => $documentId,
                'email' => Str::lower(fake()->unique()->userName()).self::DEMO_MARKER.'@gestionsegura.test',
                'birth_date' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            ]);

            $createdAt = Carbon::now()->subDays(fake()->numberBetween(0, 150))->subMinutes(fake()->numberBetween(0, 1440));

            $quote = $insured->quotes()->create([
                ...$calculation,
                'reference' => (string) Str::uuid(),
                'trip_type' => $isMultiDestination ? 'multiple' : 'direct',
                'destination_country_code' => $destinations[0]['code'],
                'destination_country_name' => collect($destinations)->pluck('name')->join(', '),
                'destinations' => $destinations,
                'departure_date' => $departureDate->toDateString(),
                'return_date' => $returnDate->toDateString(),
                'status' => QuoteStatus::Quoted,
            ]);

            $quote->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
            $quote->update(['reference' => sprintf('SEG-%d-%06d', $createdAt->year, $quote->id)]);

            $isContracted = fake()->boolean(60);

            if (! $isContracted) {
                continue;
            }

            $contractedAt = (clone $createdAt)->addMinutes(fake()->numberBetween(2, 180));

            $payment = $quote->payments()->create([
                'idempotency_key' => (string) Str::uuid(),
                'reference' => 'PAY-'.$contractedAt->format('Ymd').'-'.Str::upper(Str::random(10)),
                'status' => PaymentStatus::Approved,
                'card_brand' => fake()->randomElement(CardBrand::cases()),
                'card_last_four' => fake()->numerify('####'),
                'amount' => $quote->total,
                'currency' => 'USD',
                'authorization_code' => Str::upper(Str::random(8)),
                'failure_code' => null,
                'paid_at' => $contractedAt,
            ]);
            $payment->forceFill(['created_at' => $contractedAt, 'updated_at' => $contractedAt])->save();

            $quote->update(['status' => QuoteStatus::Contracted, 'contracted_at' => $contractedAt]);
            $quote->forceFill(['updated_at' => $contractedAt])->save();

            $user = $provisionAccount->execute($insured, $insured->email, "{$insured->first_name} {$insured->last_name}");

            $credentials[] = [
                'cliente' => "{$insured->first_name} {$insured->last_name}",
                'usuario' => $user->username,
                'password' => $documentId,
                'referencia' => $quote->reference,
            ];
        }

        $this->command?->info(sprintf(
            'ContractsDemoSeeder: %d cotizaciones creadas (%d contratadas con cuenta de cliente).',
            $recordCount,
            count($credentials),
        ));

        if ($this->command && $credentials !== []) {
            $this->command->info('Credenciales de clientes de prueba (usuario y contraseña = documento de identidad):');
            $this->command->table(
                ['Cliente', 'Usuario', 'Contraseña', 'Cotización'],
                array_slice($credentials, 0, 15),
            );
        }
    }

    private function generateValidCedula(): string
    {
        $province = str_pad((string) fake()->numberBetween(1, 24), 2, '0', STR_PAD_LEFT);
        $thirdDigit = (string) fake()->numberBetween(0, 5);
        $base = $province.$thirdDigit.fake()->numerify('######');

        $sum = 0;
        for ($index = 0; $index < 9; $index++) {
            $digit = (int) $base[$index];
            $product = $digit * ($index % 2 === 0 ? 2 : 1);
            $sum += $product > 9 ? $product - 9 : $product;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $base.$checkDigit;
    }
}
