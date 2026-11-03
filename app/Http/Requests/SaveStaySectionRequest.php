<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveStaySectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $rules = [
            'action' => ['nullable', 'in:add_photo'],
            'published' => ['required', 'boolean'],
            'translations' => ['required', 'array:fr,en,it'],
            'photos' => ['nullable', 'array', 'max:100'],
            'photos.*' => ['required', 'integer', 'distinct', 'exists:photos,id'],
        ];
        foreach (['fr', 'en', 'it'] as $locale) {
            $rules["translations.$locale"] = ['sometimes', 'array:title,body'];
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.body"] = [$locale === 'fr' ? 'required' : "required_with:translations.$locale.title", 'nullable', 'string', 'max:50000'];
        }

        return $rules;
    }
}
