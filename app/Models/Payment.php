<?php

namespace App\Models;

use App\Enums\CardBrand;
use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'idempotency_key',
        'reference',
        'status',
        'card_brand',
        'card_last_four',
        'amount',
        'currency',
        'authorization_code',
        'failure_code',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'card_brand' => CardBrand::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
