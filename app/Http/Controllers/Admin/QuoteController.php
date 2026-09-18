<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $quotes = Quote::query()
            ->with(['insured.user', 'latestPayment'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('destination_country_name', 'like', "%{$search}%")
                        ->orWhereHas('insured', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('document_id', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalQuotes = Quote::count();
        $totalContracted = Quote::where('status', QuoteStatus::Contracted)->count();

        $stats = [
            'total' => $totalQuotes,
            'contracted' => $totalContracted,
            'revenue' => (float) Quote::where('status', QuoteStatus::Contracted)->sum('total'),
            'conversion_rate' => $totalQuotes > 0 ? round($totalContracted / $totalQuotes * 100, 1) : 0.0,
        ];

        $quoteDetails = $quotes->getCollection()->mapWithKeys(
            fn (Quote $quote) => [$quote->reference => QuoteResource::make($quote)->resolve($request)],
        );

        return view('admin.quotes.index', [
            'quotes' => $quotes,
            'search' => $search,
            'status' => $status,
            'statuses' => QuoteStatus::cases(),
            'stats' => $stats,
            'quoteDetails' => $quoteDetails,
        ]);
    }
}
