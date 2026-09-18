<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    protected $fillable = [
        'insured_id',
        'reference',
        'trip_type',
        'destination_country_code',
        'destination_country_name',
        'destinations',
        'region',
        'departure_date',
        'return_date',
        'days',
        'daily_rate',
        'surcharge_percentage',
        'subtotal',
        'surcharge_amount',
        'total',
        'status',
        'contracted_at',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'destinations' => 'array',
            'days' => 'integer',
            'daily_rate' => 'decimal:2',
            'surcharge_percentage' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'surcharge_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => QuoteStatus::class,
            'contracted_at' => 'datetime',
        ];
    }

    public function insured(): BelongsTo
    {
        return $this->belongsTo(Insured::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }
}
