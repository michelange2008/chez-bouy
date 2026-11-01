<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
        ];
    }
}
