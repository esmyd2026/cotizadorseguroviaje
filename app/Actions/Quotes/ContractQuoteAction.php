<?php

namespace App\Actions\Quotes;

use App\Enums\QuoteStatus;
use App\Exceptions\QuoteAlreadyContractedException;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class ContractQuoteAction
{
    public function execute(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote) {
            // Row lock closes the check-then-act race between two simultaneous
            // contract requests for the same quote.
            $locked = Quote::whereKey($quote->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status === QuoteStatus::Contracted) {
                throw new QuoteAlreadyContractedException($locked);
            }

            $locked->update([
                'status' => QuoteStatus::Contracted,
                'contracted_at' => now(),
            ]);

            return $locked;
        });
    }
}
