<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractQuoteRequest extends FormRequest
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
            'confirmation' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation.accepted' => 'Debes confirmar que los datos ingresados son correctos.',
        ];
    }
}
