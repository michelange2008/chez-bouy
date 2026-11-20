@extends('admin.layout')
@section('title', __('admin.rentals'))
@section('admin-content')
    <div class="users-toolbar">
        <h2>{{ __('admin.rentals') }}</h2>
        <a class="media-button" href="{{ route('admin.rentals.create') }}">{{ __('rentals.create') }}</a>
    </div>
    <div class="grid gap-4">
        @forelse($rentals as $rental)
            @php
                $calendarUrl = route('admin.calendar', ['month' => $rental->arrival->format('Y-m')]);
                if ($rental->calendar_event_id !== null) {
                    $calendarUrl .= '#calendar-event-'.$rental->calendar_event_id.'-'.$rental->arrival->format('Y-m');
                }
                $isFamily = $rental->type === 'family';
                $isPaidRental = $rental->type === 'rental';
            @endphp
            <article class="media-panel p-0!">
                <header class="flex items-stretch bg-gray-100 p-2! max-sm:flex-nowrap! max-sm:gap-2!">
                    <span title="{{ __('rentals.types.'.$rental->type) }}"
                        style="background-color: {{ \App\Models\Rental::TYPE_COLORS[$rental->type] }}" @class([
                        'flex h-7 w-10 shrink-0 items-center justify-center self-center ms-3 rounded-full max-sm:ms-0 max-sm:self-start text-base font-bold',
                        'text-black' => $isFamily,
                        'text-white' => ! $isFamily,
                    ])>
                        <span aria-hidden="true">{{ match ($rental->type) { 'family' => 'F', 'rental' => 'L', 'exchange' => 'E' } }}</span>
                        <span class="sr-only">{{ __('rentals.types.'.$rental->type) }}</span>
                    </span>
                    <div class="flex min-w-0 flex-1 flex-wrap items-center justify-between gap-x-4 gap-y-2 px-3 py-0.5 max-sm:flex-nowrap max-sm:items-start max-sm:gap-x-2 max-sm:p-0">
                        <div class="flex min-w-0 flex-wrap items-baseline gap-x-3 gap-y-1 max-sm:flex-1 max-sm:flex-col max-sm:items-start">
                            <h3 class="m-0! wrap-anywhere font-bold max-sm:leading-7!">
                                @if($rental->user)
                                    <a class="no-underline!" href="{{ route('admin.users.edit', $rental->user) }}">{{ $rental->guestName() }}</a>
                                @else
                                    {{ $rental->guestName() }}
                                @endif
                            </h3>
                            <a class="italic no-underline! max-sm:text-xs" href="{{ $calendarUrl }}">{{ __('rentals.dates', ['arrival' => $rental->arrival->format('d/m/Y'), 'departure' => $rental->departure->format('d/m/Y')]) }}</a>
                            @if($isPaidRental)
                                <span class="text-sm">{{ $rental->amount === null ? __('rentals.free') : Number::currency((float) $rental->amount, 'EUR', locale: app()->getLocale()) }}</span>
                            @endif
                            @if(! $isFamily && $rental->departure->toDateString() < $today)
                                <span class="text-sm">{{ __('rentals.past') }}</span>
                            @endif
                        </div>
                        <div class="ms-auto flex items-start gap-1.5 max-sm:flex-col max-sm:items-end">
                            <a class="media-button size-8 min-h-0! shrink-0 p-0!" href="{{ route('admin.rentals.edit', $rental) }}" title="{{ __('notes.modify') }}" aria-label="{{ __('notes.modify') }} — {{ $rental->guestName() }}">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 3 5 5-12 12H4v-5L16 3Z M13 6l5 5"/></svg>
                            </a>
                            <details class="media-delete mt-0! max-sm:max-w-24 max-sm:text-right">
                                <summary class="media-button media-button-danger ms-auto size-8 min-h-0! p-0!" title="{{ __('notes.delete') }}" aria-label="{{ __('notes.delete') }} — {{ $rental->guestName() }}">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18 M9 6V3h6v3 M5 6l1 15h12l1-15 M10 10v7 M14 10v7"/></svg>
                                </summary>
                                <form class="mt-2" method="post" action="{{ route('admin.rentals.destroy', $rental) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="media-button media-button-danger max-sm:px-2! max-sm:text-xs!">{{ __('notes.confirm_delete') }}</button>
                                </form>
                            </details>
                        </div>
                    </div>
                </header>
                @unless($isFamily)
                    <dl class="grid gap-x-5 gap-y-2 border-t border-(--color-border) px-4 py-3 text-sm sm:grid-cols-2 xl:grid-cols-4">
                        @if($isPaidRental)
                            @foreach(['deposit', 'balance'] as $payment)
                                @php
                                    $date = $rental->{$payment.'_date'};
                                    $amount = $rental->{$payment.'_amount'};
                                @endphp
                                <div>
                                    <dt class="font-semibold">{{ __('rentals.'.$payment) }}</dt>
                                    <dd>{{ $date?->format('d/m/Y') ?? '—' }} · {{ $amount === null ? '—' : Number::currency((float) $amount, 'EUR', locale: app()->getLocale()) }}</dd>
                                </div>
                            @endforeach
                        @endif
                        @foreach(\App\Models\Rental::CHECKLIST as $field)
                            @continue($rental->type === 'exchange' && $field === 'contract_sent')
                            <div>
                                <dt class="font-semibold">{{ __('rentals.fields.'.$field) }}</dt>
                                <dd>{{ $rental->$field ? __('users.yes') : __('users.no') }}</dd>
                            </div>
                        @endforeach
                        <div class="sm:col-span-2">
                            <dt class="font-semibold">{{ __('rentals.fields.remarks') }}</dt>
                            <dd class="wrap-anywhere whitespace-pre-wrap">{{ $rental->remarks ?? '—' }}</dd>
                        </div>
                    </dl>
                @endunless
            </article>
        @empty
            <p>{{ __('rentals.empty') }}</p>
        @endforelse
    </div>
    @if($rentals->hasPages())
        <nav class="users-pagination" aria-label="{{ __('users.pagination') }}">
            @if($rentals->previousPageUrl())<a href="{{ $rentals->previousPageUrl() }}" rel="prev">{{ __('users.previous') }}</a>@endif
            <span>{{ __('users.page', ['current' => $rentals->currentPage(), 'last' => $rentals->lastPage(), 'total' => $rentals->total()]) }}</span>
            @if($rentals->nextPageUrl())<a href="{{ $rentals->nextPageUrl() }}" rel="next">{{ __('users.next') }}</a>@endif
        </nav>
    @endif
@endsection
