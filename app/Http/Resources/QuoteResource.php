<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'trip_type' => $this->trip_type,
            'insured' => [
                'first_name' => $this->insured->first_name,
                'last_name' => $this->insured->last_name,
                'document_type' => $this->insured->document_type->value,
                'document_type_label' => $this->insured->document_type->label(),
                'document_id' => $this->insured->document_id,
                'email' => $this->insured->email,
                'phone' => $this->insured->phone,
            ],
            'destination' => [
                'country_code' => $this->destination_country_code,
                'country_name' => $this->destination_country_name,
                'region' => $this->region,
            ],
            'destinations' => $this->destinations ?? [[
                'code' => $this->destination_country_code,
                'name' => $this->destination_country_name,
                'region' => $this->region,
            ]],
            'trip' => [
                'departure_date' => $this->departure_date->toDateString(),
                'return_date' => $this->return_date->toDateString(),
                'days' => $this->days,
            ],
            'pricing' => [
                'daily_rate' => (float) $this->daily_rate,
                'surcharge_percentage' => (float) $this->surcharge_percentage,
                'subtotal' => (float) $this->subtotal,
                'surcharge_amount' => (float) $this->surcharge_amount,
                'total' => (float) $this->total,
            ],
            'payment' => $this->when(
                $this->relationLoaded('latestPayment') && $this->latestPayment !== null,
                fn (): array => [
                    'reference' => $this->latestPayment->reference,
                    'status' => $this->latestPayment->status->value,
                    'status_label' => $this->latestPayment->status->label(),
                    'card_brand' => $this->latestPayment->card_brand->value,
                    'card_brand_label' => $this->latestPayment->card_brand->label(),
                    'card_last_four' => $this->latestPayment->card_last_four,
                    'amount' => (float) $this->latestPayment->amount,
                    'currency' => $this->latestPayment->currency,
                    'authorization_code' => $this->latestPayment->authorization_code,
                    'paid_at' => $this->latestPayment->paid_at?->toIso8601String(),
                ],
            ),
            'account' => $this->when(
                $this->status->value === 'contracted' && $this->insured->relationLoaded('user') && $this->insured->user !== null,
                fn (): array => [
                    'username' => $this->insured->user->username,
                    'password' => $this->insured->document_id,
                    'note' => 'Tu usuario y tu contraseña son tu número de identificación. Puedes cambiarla luego desde "¿Olvidaste tu contraseña?".',
                ],
            ),
            'contracted_at' => $this->contracted_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
