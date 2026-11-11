@extends('admin.layout')
@section('title', __('calendar.title'))
@section('admin-content')
    <div class="users-toolbar">
        <h2>{{ __('calendar.title') }}</h2>
        @if($configured)
            <a class="media-button" href="{{ route('admin.calendar.create') }}">{{ __('calendar.add') }}</a>
        @endif
    </div>
    @unless($configured)
        <p>{{ __('calendar.setup') }}</p>
    @endunless
    <div class="grid gap-5">
        @foreach($months as $month)
            <section class="calendar-month">
                <h3>{{ ucfirst($month['date']->translatedFormat('F Y')) }}</h3>
                <ul>
                    @forelse($month['events'] as $event)
                        @php
                            $start = $event['start'];
                            $end = $event['end'];
                            $fullDay = $event['fullday'] ?? false;
                            $dateFormat = $start->isSameMonth($end) ? 'D j' : 'D j M Y';
                            $color = $event['color'] ?? null;
                        @endphp
                        <li class="calendar-row">
                            <span class="calendar-color" aria-hidden="true"
                                @if(is_string($color) && preg_match(\App\InfomaniakCalendar::COLOR_PATTERN, $color)) style="background-color: {{ $color }}" @endif></span>
                            <div class="calendar-dates">
                                <time datetime="{{ $fullDay ? $start->toDateString() : $start->toIso8601String() }}" title="{{ $start->format('d/m/Y') }}">{{ $start->translatedFormat($dateFormat) }}@unless($fullDay) {{ $start->format('H:i') }}@endunless</time>
                                @if(!$start->isSameDay($end) || !$fullDay)
                                    – <time datetime="{{ $fullDay ? $end->toDateString() : $end->toIso8601String() }}" title="{{ $end->format('d/m/Y') }}">{{ $start->isSameDay($end) ? '' : $end->translatedFormat($dateFormat) }}@unless($fullDay) {{ $end->format('H:i') }}@endunless</time>
                                @endif
                            </div>
                            <span class="calendar-title">{{ $event['title'] }}</span>
                            <div class="calendar-actions">
                                <a class="users-icon" href="{{ route('admin.calendar.edit', $event['id']) }}" aria-label="{{ __('notes.modify') }} — {{ $event['title'] }}" title="{{ __('notes.modify') }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m16 3 5 5-12 12H4v-5L16 3Z M13 6l5 5"/></svg>
                                </a>
                                <details>
                                    <summary class="users-icon" aria-label="{{ __('notes.delete') }} — {{ $event['title'] }}" title="{{ __('notes.delete') }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18 M9 6V3h6v3 M5 6l1 15h12l1-15 M10 10v7 M14 10v7"/></svg>
                                    </summary>
                                    <form method="post" action="{{ route('admin.calendar.destroy', $event['id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="media-button media-button-danger">{{ __('notes.confirm_delete') }}</button>
                                    </form>
                                </details>
                            </div>
                        </li>
                    @empty
                        @if($configured)
                            <li class="calendar-empty">{{ __('calendar.empty') }}</li>
                        @endif
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
    <nav class="users-pagination" aria-label="{{ __('admin.calendar') }}">
        <a class="media-button" rel="prev" href="{{ route('admin.calendar', ['month' => $previousPage->format('Y-m')]) }}">{{ __('users.previous') }}</a>
        <a class="media-button" rel="next" href="{{ route('admin.calendar', ['month' => $nextPage->format('Y-m')]) }}">{{ __('users.next') }}</a>
    </nav>
@endsection
