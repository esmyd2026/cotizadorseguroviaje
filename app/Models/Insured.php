<?php

namespace App\Models;

use App\Enums\DocumentType;
use Database\Factories\InsuredFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Insured extends Model
{
    /** @use HasFactory<InsuredFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'document_type',
        'document_id',
        'email',
        'phone',
        'birth_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'document_type' => DocumentType::class,
        ];
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
