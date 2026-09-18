<?php

namespace App\Http\Controllers\Customer;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.quotes.index');
        }

        $quotes = Quote::query()
            ->where('insured_id', $user->insured_id)
            ->with(['insured.user', 'latestPayment'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => $quotes->total(),
            'contracted' => Quote::where('insured_id', $user->insured_id)
                ->where('status', QuoteStatus::Contracted)
                ->count(),
        ];

        $quoteDetails = $quotes->getCollection()->mapWithKeys(
            fn (Quote $quote) => [$quote->reference => QuoteResource::make($quote)->resolve($request)],
        );

        return view('customer.quotes.index', [
            'quotes' => $quotes,
            'stats' => $stats,
            'quoteDetails' => $quoteDetails,
        ]);
    }
}
