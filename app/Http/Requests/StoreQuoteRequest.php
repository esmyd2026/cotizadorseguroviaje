<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
use App\Services\CountriesService;
use App\Services\EcuadorianIdentityService;
use App\Services\PhoneNumberService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'trip_type' => ['required', Rule::in(['direct', 'multiple'])],
            'destination_country_codes' => ['required', 'array', 'min:1', 'max:5'],
            'destination_country_codes.*' => [
                'required',
                'string',
                'size:2',
                'distinct',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! app(CountriesService::class)->findByCode($value)) {
                        $fail('Uno de los destinos seleccionados no es válido.');
                    }
                },
            ],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
            'first_name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\pL\s'-]+$/u"],
            'last_name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\pL\s'-]+$/u"],
            'document_type' => ['required', Rule::enum(DocumentType::class)],
            'document_id' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $documentType = DocumentType::tryFrom((string) $this->input('document_type'));

                    if ($documentType === DocumentType::Cedula && ! app(EcuadorianIdentityService::class)->isValidCedula((string) $value)) {
                        $fail('Ingresa una cédula ecuatoriana válida.');
                    }

                    if ($documentType === DocumentType::Passport && ! preg_match('/^[A-Za-z0-9]{6,20}$/', (string) $value)) {
                        $fail('El pasaporte debe tener entre 6 y 20 letras o números.');
                    }
                },
            ],
            'email' => ['required', 'email', 'max:255'],
            'phone_country_code' => [
                'required',
                'string',
                'size:2',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! app(CountriesService::class)->findByCode($value)) {
                        $fail('El código de país del teléfono no es válido.');
                    }
                },
            ],
            'phone_number' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $countryCode = $this->input('phone_country_code');

                    if (! is_string($countryCode) || strlen($countryCode) !== 2) {
                        return;
                    }

                    if (! app(PhoneNumberService::class)->isValidNumber($value, $countryCode)) {
                        $fail('Ingresa un número de teléfono válido para el país seleccionado.');
                    }
                },
            ],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->uppercaseWords((string) $this->input('first_name')),
            'last_name' => $this->uppercaseWords((string) $this->input('last_name')),
            'email' => mb_strtoupper(trim((string) $this->input('email')), 'UTF-8'),
        ]);

        if (! $this->has('destination_country_codes') && $this->filled('destination_country_code')) {
            $this->merge([
                'trip_type' => 'direct',
                'destination_country_codes' => [$this->input('destination_country_code')],
            ]);
        }
    }

    private function uppercaseWords(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? '';

        return mb_strtoupper($normalized, 'UTF-8');
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $destinations = $this->input('destination_country_codes', []);

            if (! is_array($destinations)) {
                return;
            }

            if ($this->input('trip_type') === 'direct' && count($destinations) !== 1) {
                $validator->errors()->add('destination_country_codes', 'Un viaje directo debe tener un solo destino.');
            }

            if ($this->input('trip_type') === 'multiple' && count($destinations) < 2) {
                $validator->errors()->add('destination_country_codes', 'Agrega al menos dos países para un viaje con varias paradas.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'trip_type.required' => 'Indica si visitarás uno o varios países.',
            'trip_type.in' => 'El tipo de viaje seleccionado no es válido.',
            'destination_country_codes.required' => 'Selecciona al menos un país de destino.',
            'destination_country_codes.min' => 'Selecciona al menos un país de destino.',
            'destination_country_codes.max' => 'Puedes incluir hasta cinco países en el itinerario.',
            'destination_country_codes.*.distinct' => 'No repitas países dentro del itinerario.',
            'destination_country_codes.*.size' => 'Uno de los destinos seleccionados no es válido.',
            'departure_date.required' => 'Selecciona la fecha de salida.',
            'departure_date.after_or_equal' => 'La fecha de salida no puede ser en el pasado.',
            'return_date.required' => 'Selecciona la fecha de regreso.',
            'return_date.after_or_equal' => 'La fecha de regreso debe ser igual o posterior a la fecha de salida.',
            'first_name.required' => 'Ingresa tu nombre.',
            'first_name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'first_name.regex' => 'El nombre solo puede contener letras.',
            'last_name.required' => 'Ingresa tu apellido.',
            'last_name.min' => 'El apellido debe tener al menos 2 caracteres.',
            'last_name.regex' => 'El apellido solo puede contener letras.',
            'document_type.required' => 'Selecciona el tipo de identificación.',
            'document_type.in' => 'El tipo de identificación seleccionado no es válido.',
            'document_id.required' => 'Ingresa tu número de identificación.',
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'phone_country_code.required' => 'Selecciona el código de tu país.',
            'phone_number.required' => 'Ingresa tu número de teléfono.',
            'birth_date.required' => 'Ingresa tu fecha de nacimiento.',
            'birth_date.before_or_equal' => 'Debes ser mayor de edad para contratar el seguro.',
        ];
    }
}
