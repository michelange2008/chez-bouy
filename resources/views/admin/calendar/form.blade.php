@extends('admin.layout')
@section('title', __($event ? 'calendar.edit' : 'calendar.add'))
@section('admin-content')
    <h2>{{ __($event ? 'calendar.edit' : 'calendar.add') }}</h2>
    <form method="post" class="media-form max-w-3xl" action="{{ $event ? route('admin.calendar.update', $event['id']) : route('admin.calendar.store') }}">
        @csrf
        @if($event) @method('PATCH') @endif
        <section class="media-panel">
            <label for="event-title">{{ __('notes.title') }}</label>
            <input id="event-title" name="title" value="{{ old('title', $event['title'] ?? '') }}" maxlength="255" required>
            @php
                $color = old('color', $event['color'] ?? null);
                $hasColor = is_string($color) && preg_match(\App\InfomaniakCalendar::COLOR_PATTERN, $color);
                $fullDay = (bool) old('fullday', $event['fullday'] ?? empty($event));
            @endphp
            <div class="calendar-options">
                <div class="flex items-center gap-2">
                    <label for="event-color">{{ __('calendar.color') }}</label>
                    <input type="hidden" name="color" value="">
                    <input type="color" id="event-color" name="color"
                           data-default-color="#4060ba" data-custom-color="{{ $hasColor ? '1' : '0' }}"
                           value="{{ $hasColor ? $color : '#4060ba' }}">
                </div>
                <input type="hidden" name="fullday" value="0">
                <label class="media-checkbox"><input type="checkbox" id="event-fullday" name="fullday" value="1" @checked($fullDay)> {{ __('calendar.full_day') }}</label>
            </div>
            <button type="button" class="media-button" data-reset-color hidden>{{ __('calendar.default_color') }}</button>
            <div class="grid gap-x-5 sm:grid-cols-2">
                @foreach(['start', 'end'] as $field)
                    <div class="min-w-0">
                        <label for="event-{{ $field }}">{{ __('calendar.'.$field) }}</label>
                        <input type="{{ $fullDay ? 'date' : 'datetime-local' }}" data-event-date id="event-{{ $field }}" name="{{ $field }}" value="{{ old($field, $event[$field] ?? '') }}" @required(!$fullDay || $field === 'start') @if($field === 'end') aria-describedby="event-end-help" @endif>
                        @if($field === 'end')
                            <p id="event-end-help" class="media-help" data-full-day-label @unless($fullDay) hidden @endunless>{{ __('calendar.last_day') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
        <div class="users-form-actions">
            <a class="media-button" href="{{ route('admin.calendar') }}">{{ __('notes.cancel') }}</a>
            <button class="media-button">{{ __('notes.save') }}</button>
        </div>
    </form>
@endsection
