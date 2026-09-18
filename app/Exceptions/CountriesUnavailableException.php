<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CountriesUnavailableException extends Exception
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('No pudimos obtener el listado de países en este momento.', previous: $previous);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 503);
    }
}
