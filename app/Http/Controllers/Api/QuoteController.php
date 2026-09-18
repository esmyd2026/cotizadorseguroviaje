<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payments\ProcessSimulatedPaymentAction;
use App\Actions\Quotes\CreateQuoteAction;
use App\Enums\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessQuotePaymentRequest;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class QuoteController extends Controller
{
    public function store(StoreQuoteRequest $request, CreateQuoteAction $action): JsonResponse
    {
        $quote = $action->execute($request->validated());

        return QuoteResource::make($quote->load('insured.user'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Quote $quote): QuoteResource
    {
        return QuoteResource::make($quote->load('insured.user'));
    }

    public function payment(
        ProcessQuotePaymentRequest $request,
        Quote $quote,
        ProcessSimulatedPaymentAction $action,
    ): QuoteResource {
        $action->execute($quote, $request->validated());

        return QuoteResource::make($quote->fresh()->load(['insured.user', 'latestPayment']));
    }

    public function pdf(Quote $quote): Response
    {
        $quote->load('insured');

        return Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'regionLabel' => Region::from($quote->region)->label(),
        ])->download("cotizacion-{$quote->reference}.pdf");
    }
}
