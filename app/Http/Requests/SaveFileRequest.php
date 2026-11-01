<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'upload' => [$this->route('file') ? 'nullable' : 'required', 'file', 'max:10240'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'locale' => ['required', Rule::in(array_keys(config('languages.supported')))],
        ];
    }
}
