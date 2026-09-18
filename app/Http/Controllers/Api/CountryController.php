<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CountriesService;
use App\Services\PhoneNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct(
        private readonly CountriesService $countries,
        private readonly PhoneNumberService $phoneNumbers,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $search = (string) $request->query('search', '');

        $countries = $search === ''
            ? $this->countries->all()
            : $this->countries->search($search);

        return response()->json([
            'data' => $countries->map(fn (array $country) => [
                ...$country,
                'dial_code' => $this->phoneNumbers->dialCodeFor($country['code']),
                'phone_length' => $this->phoneNumbers->possibleLengthFor($country['code']),
            ])->values(),
        ]);
    }
}
