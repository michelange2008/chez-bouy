<?php

namespace App\Models;

use Database\Factories\RentalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

#[Fillable([
    'arrival', 'departure', 'type', 'amount', 'deposit_date', 'deposit_amount', 'balance_date',
    'balance_amount', 'contract_sent', 'contract_signed', 'marie_notified', 'mail_envoye', 'concierge_notified', 'guide_sent', 'codes_sent',
    'brings_linen', 'does_cleaning', 'remarks',
])]
class Rental extends Model
{
    /** @use HasFactory<RentalFactory> */
    use HasFactory;

    public const TYPES = ['rental', 'exchange', 'family'];

    public const TYPES_WITH_CODES = ['rental', 'exchange'];

    public const CODE_REMINDER_DAYS = 7;

    public const TYPE_COLORS = [
        'rental' => '#d40000',
        'exchange' => '#aa00d4',
        'family' => '#55d400',
    ];

    public const CHECKLIST = [
        'contract_sent', 'contract_signed', 'marie_notified', 'mail_envoye', 'concierge_notified', 'guide_sent', 'codes_sent', 'brings_linen', 'does_cleaning',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guestName(): string
    {
        return $this->user?->fullName() ?? $this->guest_name;
    }

    public function paymentSummary(string $payment): array
    {
        $date = $this->{$payment.'_date'};
        $amount = $this->{$payment.'_amount'};
        $formattedAmount = $amount === null ? '—' : Number::currency((float) $amount, 'EUR', locale: app()->getLocale());

        return [
            'date' => $date?->format('d/m/Y') ?? '—',
            'amount' => $formattedAmount,
            'complete' => $date !== null && $amount !== null,
        ];
    }

    public function paymentTotalStatus(): ?string
    {
        if ($this->balance_date === null || $this->balance_amount === null) {
            return null;
        }

        $toCents = static fn (?string $amount): int => (int) str_replace('.', '', $amount ?? '0.00');
        $total = $toCents($this->deposit_amount) + $toCents($this->balance_amount);

        return $this->amount !== null && $total === $toCents($this->amount) ? 'matched' : 'mismatch';
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
