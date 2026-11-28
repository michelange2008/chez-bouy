@extends('layout')
@section('title', __('availability.title'))
@section('content')
    <div class="[&>.language-switcher]:mb-0! max-sm:[&>.language-switcher]:justify-start!">
        @include('partials.language-switcher')
    </div>
    @php
        $availabilityStyles = [
            'available' => 'bg-green-700',
            'occupied' => 'invisible',
            'arrival' => 'bg-[linear-gradient(90deg,#15803d_50%,#b91c1c_50%)]',
            'departure' => 'bg-[linear-gradient(90deg,#b91c1c_50%,#15803d_50%)]',
        ];
    @endphp
    <section class="panel mx-auto mt-0! max-w-xl pt-0!">
        <div class="sticky top-0 z-10 flex flex-wrap items-baseline justify-between gap-x-3 bg-(--color-surface) py-2">
            <h1 class="m-0! text-3xl!">{{ __('availability.title') }}</h1>
            <a href="{{ route('home') }}">← {{ __('availability.home') }}</a>
        </div>

        @isset($weeks)
            <ul class="mt-4 grid grid-cols-2 gap-2 text-sm">
                @foreach($availabilityStyles as $status => $style)
                    <li class="flex items-center gap-2">
                        @if($status === 'occupied')
                            <span class="w-3 shrink-0 text-center tabular-nums line-through decoration-2" aria-hidden="true">1</span>
                        @else
                            <span class="h-4 w-8 shrink-0 {{ $style }}" aria-hidden="true"></span>
                        @endif
                        {{ __('availability.'.$status) }}
                    </li>
                @endforeach
            </ul>
            <div class="grid gap-6" data-availability-months>
                <section data-availability-month>
                    <h2 class="mt-4 mb-2 text-center capitalize">{{ $month->translatedFormat('F Y') }}</h2>
                    <table class="w-full table-fixed border-collapse text-center" aria-label="{{ $month->translatedFormat('F Y') }}">
                        <thead>
                            <tr>
                                @for($column = 0; $column < 7; $column++)
                                    @php($weekday = $weekdayStart->addDays($column))
                                    <th scope="col" class="pb-2 text-xs sm:text-sm" abbr="{{ $weekday->translatedFormat('l') }}">{{ $weekday->translatedFormat('D') }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weeks as $week)
                                <tr>
                                    @foreach($week as $day)
                                        <td class="border-t border-(--color-border) px-0 py-2">
                                            @if($day !== null)
                                                <time datetime="{{ $day['date']->toDateString() }}" class="flex flex-col items-center gap-1 tabular-nums" data-availability="{{ $day['status'] }}">
                                                    <span @class(['line-through decoration-2' => $day['status'] === 'occupied'])>{{ $day['date']->day }}</span>
                                                    <span class="h-4 w-full {{ $availabilityStyles[$day['status']] }}" aria-hidden="true"></span>
                                                    <span class="sr-only">{{ __('availability.'.$day['status']) }}</span>
                                                </time>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            </div>
        @else
            <p role="status">{{ __('availability.unavailable') }}</p>
        @endisset

        <div class="mt-4 text-center">
            <a class="button" data-availability-next rel="next" href="{{ route('availability', ['month' => $month->addMonth()->format('Y-m')]) }}">{{ __('availability.next') }} →</a>
            <p data-availability-error role="status" hidden>{{ __('availability.unavailable') }}</p>
        </div>
    </section>
@endsection
