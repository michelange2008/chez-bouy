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
            'action' => ['nullable', 'in:fr,en,it'],
            'published' => ['required', 'boolean'],
            'translations' => ['required', 'array:fr,en,it'],
            'photos' => ['nullable', 'array:fr,en,it'],
        ];
        foreach (['fr', 'en', 'it'] as $locale) {
            $rules["photos.$locale"] = ['nullable', 'array', 'max:100'];
            $rules["photos.$locale.*"] = ['required', 'integer', 'distinct', 'exists:photos,id'];
            $rules["translations.$locale"] = ['sometimes', 'array:title,body'];
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.body"] = [$locale === 'fr' ? 'required' : "required_with:translations.$locale.title", 'nullable', 'string', 'max:50000'];
        }

        if (($this->route('page') ?? $this->route('section')?->page) === 'home') {
            $rules['photos'] = ['prohibited'];
            $rules['action'] = ['prohibited'];
        }

        return $rules;
    }
}
