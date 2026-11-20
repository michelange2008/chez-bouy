<?php

namespace App\Models;

use Database\Factories\RentalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'arrival', 'departure', 'type', 'amount', 'deposit_date', 'deposit_amount', 'balance_date',
    'balance_amount', 'contract_sent', 'contract_signed', 'marie_notified', 'guide_sent', 'codes_sent',
    'brings_linen', 'does_cleaning', 'remarks',
])]
class Rental extends Model
{
    /** @use HasFactory<RentalFactory> */
    use HasFactory;

    public const TYPES = ['rental', 'exchange', 'family'];

    public const TYPE_COLORS = [
        'rental' => '#d40000',
        'exchange' => '#aa00d4',
        'family' => '#55d400',
    ];

    public const CHECKLIST = [
        'contract_sent', 'contract_signed', 'marie_notified', 'guide_sent', 'codes_sent', 'brings_linen', 'does_cleaning',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guestName(): string
    {
        return $this->user?->fullName() ?? $this->guest_name;
    }

    protected function casts(): array
    {
        return [
            'arrival' => 'date',
            'departure' => 'date',
            'deposit_date' => 'date',
            'balance_date' => 'date',
            'amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            ...array_fill_keys(self::CHECKLIST, 'boolean'),
        ];
    }
}
