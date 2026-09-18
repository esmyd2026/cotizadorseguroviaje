<?php

namespace App\Http\Requests;

use App\Models\Quote;
use App\Services\CardValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProcessQuotePaymentRequest extends FormRequest
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
            'idempotency_key' => ['required', 'uuid'],
            'card_holder' => ['required', 'string', 'min:5', 'max:100', "regex:/^[\pL\s'.-]+$/u"],
            'card_number' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $cards = app(CardValidationService::class);

                    if (! $cards->passesLuhn((string) $value)) {
                        $fail('Ingresa un número de tarjeta válido.');

                        return;
                    }

                    if ($cards->brand((string) $value) === null) {
                        $fail('La simulación acepta tarjetas Visa o Mastercard de 16 dígitos.');
                    }
                },
            ],
            'expiration_month' => ['required', 'integer', 'between:1,12'],
            'expiration_year' => ['required', 'integer', 'between:'.now()->year.','.now()->addYears(15)->year],
            'security_code' => ['required', 'string', 'regex:/^\d{3}$/'],
            'billing_email' => ['required', 'email', 'max:255'],
            'terms' => ['accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'card_number' => app(CardValidationService::class)->normalize((string) $this->input('card_number')),
            'card_holder' => mb_strtoupper(
                preg_replace('/\s+/', ' ', trim((string) $this->input('card_holder'))) ?? '',
                'UTF-8',
            ),
            'billing_email' => mb_strtoupper(trim((string) $this->input('billing_email')), 'UTF-8'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $month = (int) $this->input('expiration_month');
            $year = (int) $this->input('expiration_year');

            if ($year === now()->year && $month < now()->month) {
                $validator->errors()->add('expiration_month', 'La tarjeta está vencida.');
            }

            $quote = $this->route('quote');

            if ($quote instanceof Quote
                && mb_strtoupper($quote->insured()->value('email'), 'UTF-8') !== $this->input('billing_email')) {
                $validator->errors()->add('billing_email', 'Usa el mismo correo registrado en la cotización.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'idempotency_key.required' => 'No pudimos identificar este intento de pago. Recarga la página e inténtalo nuevamente.',
            'idempotency_key.uuid' => 'El identificador del intento de pago no es válido.',
            'card_holder.required' => 'Ingresa el nombre que aparece en la tarjeta.',
            'card_holder.min' => 'Ingresa el nombre completo que aparece en la tarjeta.',
            'card_holder.regex' => 'El nombre de la tarjeta contiene caracteres no válidos.',
            'card_number.required' => 'Ingresa el número de tarjeta.',
            'card_number.max' => 'El número de tarjeta es demasiado largo.',
            'expiration_month.required' => 'Selecciona el mes de vencimiento.',
            'expiration_month.between' => 'Selecciona un mes de vencimiento válido.',
            'expiration_year.required' => 'Selecciona el año de vencimiento.',
            'expiration_year.between' => 'Selecciona un año de vencimiento válido.',
            'security_code.required' => 'Ingresa el código de seguridad.',
            'security_code.regex' => 'El código de seguridad debe tener 3 dígitos.',
            'billing_email.required' => 'Ingresa el correo para el comprobante.',
            'billing_email.email' => 'Ingresa un correo electrónico válido.',
            'terms.accepted' => 'Debes aceptar los términos de la simulación para continuar.',
        ];
    }
}
