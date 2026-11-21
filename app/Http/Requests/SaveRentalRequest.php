<?php

namespace App\Http\Requests;

use App\Models\Rental;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('new_email'))) {
            $this->merge(['new_email' => Str::lower(trim($this->input('new_email')))]);
        }
    }

    public function rules(): array
    {
        $stay = [
            'arrival' => ['required', 'date_format:Y-m-d'],
            'departure' => ['required', 'date_format:Y-m-d', 'after_or_equal:arrival'],
            'type' => ['required', Rule::in(Rental::TYPES)],
            'amount' => ['exclude_unless:type,rental', 'required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
        ];

        if ($this->route('rental')) {
            return [
                ...$stay,
                ...array_fill_keys(['user_id', 'new_name', 'new_first_name', 'new_email'], ['prohibited']),
            ];
        }

        return [
            ...$stay,
            'user_id' => $this->input('user_id') === 'new'
                ? ['required', Rule::in(['new'])]
                : ['required', 'integer', 'exists:users,id'],
            'new_name' => ['exclude_unless:user_id,new', 'required', 'string', 'max:255'],
            'new_first_name' => ['exclude_unless:user_id,new', 'nullable', 'string', 'max:255'],
            'new_email' => ['exclude_unless:user_id,new', 'required', 'string', 'email', 'max:254', Rule::unique('users', 'email')],
        ];
    }

    /** @return array<int, \Closure(Validator): void> */
    public function after(): array
    {
        if ($this->route('rental')) {
            return [];
        }

        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['arrival', 'departure'])) {
                    return;
                }

                $overlappingStay = Rental::whereDate('arrival', '<', $this->input('departure'))
                    ->whereDate('departure', '>', $this->input('arrival'))
                    ->orderBy('arrival')
                    ->orderBy('id')
                    ->first(['arrival', 'departure']);

                if ($overlappingStay !== null) {
                    $validator->errors()->add('arrival', __('rentals.overlapping_stay', [
                        'arrival' => $overlappingStay->arrival->format('d/m/Y'),
                        'departure' => $overlappingStay->departure->format('d/m/Y'),
                    ]));
                }
            },
        ];
    }

    public static function paymentRules(): array
    {
        $money = ['numeric', 'decimal:0,2', 'max:99999999.99'];

        return [
            'deposit_date' => ['nullable', 'date_format:Y-m-d'],
            'deposit_amount' => ['nullable', ...$money, 'min:0'],
            'balance_date' => ['nullable', 'date_format:Y-m-d'],
            'balance_amount' => ['nullable', ...$money, 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return __('rentals.fields');
    }
}
