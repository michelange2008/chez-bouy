<?php

namespace App\Http\Requests;

use App\Models\Rental;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $money = ['numeric', 'decimal:0,2', 'max:99999999.99'];
        $rules = [
            'arrival' => ['required', 'date_format:Y-m-d'],
            'departure' => ['required', 'date_format:Y-m-d', 'after_or_equal:arrival'],
            'type' => ['required', Rule::in(Rental::TYPES)],
            'amount' => ['exclude_unless:type,rental', 'required', ...$money, 'min:0.01'],
            'deposit_date' => ['nullable', 'date_format:Y-m-d'],
            'deposit_amount' => ['nullable', ...$money, 'min:0'],
            'balance_date' => ['nullable', 'date_format:Y-m-d'],
            'balance_amount' => ['nullable', ...$money, 'min:0'],
            'remarks' => ['nullable', 'string', 'max:10000'],
            ...array_fill_keys(Rental::CHECKLIST, ['sometimes', 'boolean']),
        ];

        if ($this->route('rental')) {
            return [
                ...$rules,
                ...array_fill_keys(['user_id', 'new_name', 'new_first_name', 'new_email'], ['prohibited']),
            ];
        }

        return [
            ...$rules,
            'user_id' => $this->input('user_id') === 'new'
                ? ['required', Rule::in(['new'])]
                : ['required', 'integer', 'exists:users,id'],
            'new_name' => ['exclude_unless:user_id,new', 'required', 'string', 'max:255'],
            'new_first_name' => ['exclude_unless:user_id,new', 'nullable', 'string', 'max:255'],
            'new_email' => ['exclude_unless:user_id,new', 'required', 'string', 'email', 'max:254', Rule::unique('users', 'email')],
        ];
    }

    public function attributes(): array
    {
        return __('rentals.fields');
    }
}
