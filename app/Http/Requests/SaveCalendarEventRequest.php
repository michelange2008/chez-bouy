<?php

namespace App\Http\Requests;

use App\InfomaniakCalendar;
use Illuminate\Foundation\Http\FormRequest;

class SaveCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $fullDay = $this->boolean('fullday');
        $format = $fullDay ? 'date_format:Y-m-d' : 'date_format:Y-m-d\TH:i';

        return [
            'title' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:'.InfomaniakCalendar::COLOR_PATTERN],
            'fullday' => ['sometimes', 'boolean'],
            'start' => ['required', $format],
            'end' => [$fullDay ? 'nullable' : 'required', $format, $fullDay ? 'after_or_equal:start' : 'after:start'],
        ];
    }
}
