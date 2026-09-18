<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PhoneNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneValidationController extends Controller
{
    public function __construct(private readonly PhoneNumberService $phoneNumbers) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_code' => ['required', 'string', 'size:2'],
            'number' => ['required', 'string', 'max:20'],
        ]);

        $valid = $this->phoneNumbers->isValidNumber($validated['number'], $validated['country_code']);

        return response()->json([
            'data' => [
                'valid' => $valid,
                'formatted' => $valid
                    ? $this->phoneNumbers->toE164($validated['number'], $validated['country_code'])
                    : null,
            ],
        ]);
    }
}
