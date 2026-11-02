<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $locales = array_keys(config('languages.supported'));
        $rules = [
            'image' => [$this->route('photo') ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('photos.max_upload_kb'), 'dimensions:max_width='.config('photos.max_dimension').',max_height='.config('photos.max_dimension')],
            'published' => ['required', 'boolean'],
            'translations' => ['required', 'array:'.implode(',', $locales)],
            'location_id' => ['nullable', 'integer', Rule::exists('photo_locations', 'id')],
            'position' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
        ];
        foreach ($locales as $locale) {
            $rules["translations.$locale"] = ['sometimes', 'array:title,description'];
            $rules["translations.$locale.title"] = [$locale === 'fr' ? 'required' : "required_with:translations.$locale.description", 'nullable', 'string', 'max:255'];
            $rules["translations.$locale.description"] = ['nullable', 'string', 'max:50000'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'image.uploaded' => __('photos.upload_failed'),
            'image.max' => __('photos.preparation_required'),
            'image.dimensions' => __('photos.preparation_required'),
        ];
    }
}
