<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAnnuaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'fonction' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'mail' => ['nullable', 'string', 'email', 'max:255'],
            'adresse_postale' => ['nullable', 'string', 'max:2000'],
            'observations' => ['nullable', 'string', 'max:50000'],
        ];
    }
}
