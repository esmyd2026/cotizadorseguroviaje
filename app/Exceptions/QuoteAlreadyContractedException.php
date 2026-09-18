<?php

namespace App\Exceptions;

use App\Models\Quote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteAlreadyContractedException extends Exception
{
    public function __construct(Quote $quote)
    {
        parent::__construct("La cotización {$quote->reference} ya fue contratada.");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 409);
    }
}
