@extends('admin.layout')
@section('title', $showPast ? __('rentals.past_stays') : __('admin.rentals'))
@section('admin-content')
    <div class="users-toolbar">
        <h2>{{ $showPast ? __('rentals.past_stays') : __('admin.rentals') }}</h2>
        <div class="users-actions">
            <a class="media-button" href="{{ route('admin.rentals.create') }}">{{ __('rentals.create') }}</a>
            @if($showPast)
                <a class="media-button" href="{{ route('admin.rentals') }}">{{ __('rentals.current_stays') }}</a>
            @else
                <a class="media-button" href="{{ route('admin.rentals', ['past' => 1]) }}">{{ __('rentals.past_stays') }}</a>
            @endif
        </div>
    </div>
    <div class="grid gap-4">
        @forelse($rentals as $rental)
            @php
                $calendarUrl = route('admin.calendar', ['month' => $rental->arrival->format('Y-m')]);
                if ($rental->calendar_event_id !== null) {
                    $calendarUrl .= '#calendar-event-'.$rental->calendar_event_id.'-'.$rental->arrival->format('Y-m');
                }
                $isFamily = $rental->type === 'family';
                $remarksError = $errors->getBag('rentalRemarks'.$rental->id)->first('remarks');
                $remarksInput = $remarksError ? old('remarks', $rental->remarks) : $rental->remarks;
                $isPaidRental = $rental->type === 'rental';
            @endphp
            <article class="media-panel p-0!" data-rental-card>
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
                                <span class="text-sm">{{ $rental->amount === null ? '—' : Number::currency((float) $rental->amount, 'EUR', locale: app()->getLocale()) }}</span>
                            @endif
                            @if(! $isFamily && $showPast)
                                <span class="text-sm">{{ __('rentals.past') }}</span>
                            @endif
                        </div>
                        <div class="ms-auto flex items-start gap-1.5">
                            <a class="media-button size-8 min-h-0! shrink-0 p-0!" href="{{ route('admin.rentals.edit', $rental) }}" title="{{ __('notes.modify') }}" aria-label="{{ __('notes.modify') }} — {{ $rental->guestName() }}">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 3 5 5-12 12H4v-5L16 3Z M13 6l5 5"/></svg>
                            </a>
                            @if(filled($rental->user?->email))
                                <details class="media-delete mt-0! max-sm:max-w-24 max-sm:text-right">
                                    <summary class="media-button ms-auto size-8 min-h-0! p-0!" title="{{ __('rentals.send_access_email') }}" aria-label="{{ __('rentals.send_access_email') }} — {{ $rental->guestName() }}">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                                    </summary>
                                    <form class="mt-2" method="post" action="{{ route('admin.rentals.send-access-email', $rental) }}">
                                        @csrf
                                        <button class="media-button max-sm:px-2! max-sm:text-xs!">{{ __('rentals.confirm_access_email') }}</button>
                                    </form>
                                </details>
                            @endif
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
                    @if($isPaidRental)
                        @php($paymentTotalStatus = $rental->paymentTotalStatus())
                        <dl class="grid grid-cols-2 gap-x-4 border-t border-(--color-border) px-4 py-3 text-sm" data-rental-payments>
                        @foreach(['deposit', 'balance'] as $payment)
                            @php($summary = $rental->paymentSummary($payment))
                            <div class="group min-w-0" data-payment-state="{{ $payment === 'balance' && $paymentTotalStatus === 'mismatch' ? 'mismatch' : ($summary['complete'] ? 'paid' : 'pending') }}">
                                <dt class="font-semibold text-red-700 group-data-[payment-state=paid]:text-green-700">{{ __('rentals.'.$payment) }}</dt>
                                <dd>
                                    <details>
                                        <summary class="relative cursor-pointer pe-5 group-data-[payment-state=paid]:text-green-700 group-data-[payment-state=mismatch]:text-red-700">
                                            <span data-payment-date>{{ $summary['date'] }}</span>
                                            <span class="block ps-4" data-payment-amount>{{ $summary['amount'] }}</span>
                                            @if($payment === 'balance')
                                                <span class="absolute end-0 top-0 flex font-bold" data-payment-total aria-live="polite">
                                                    <span data-total-matched class="text-green-700" @if($paymentTotalStatus !== 'matched') hidden @endif title="{{ __('rentals.payment_total_matched') }}">✓<span class="sr-only">{{ __('rentals.payment_total_matched') }}</span></span>
                                                    <span data-total-mismatch class="text-red-700" @if($paymentTotalStatus !== 'mismatch') hidden @endif title="{{ __('rentals.payment_total_mismatch') }}">✕<span class="sr-only">{{ __('rentals.payment_total_mismatch') }}</span></span>
                                                </span>
                                            @endif
                                        </summary>
                                        <form method="post" action="{{ route('admin.rentals.payment', [$rental, $payment]) }}" class="mt-2 grid gap-2" data-rental-payment>
                                            @csrf
                                            @method('PATCH')
                                            <label class="min-w-0">
                                                {{ __('rentals.fields.'.$payment.'_date') }}
                                                <input class="block min-w-0 w-full px-1! sm:px-2! group-data-[payment-state=mismatch]:text-red-700!" type="date" name="{{ $payment }}_date" value="{{ $rental->{$payment.'_date'}?->toDateString() }}">
                                            </label>
                                            <label>
                                                {{ __('rentals.fields.'.$payment.'_amount') }} (€)
                                                <input class="block w-full group-data-[payment-state=mismatch]:text-red-700!" type="number" name="{{ $payment }}_amount" min="0" max="99999999.99" step="0.01" value="{{ $rental->{$payment.'_amount'} }}">
                                            </label>
                                            <button type="submit" class="media-button justify-self-start">{{ __('notes.save') }}</button>
                                            <span role="alert" hidden>{{ __('rentals.checklist_update_failed') }}</span>
                                        </form>
                                    </details>
                                </dd>
                            </div>
                        @endforeach
                        </dl>
                    @endif
                    <dl class="grid grid-cols-2 gap-x-5 gap-y-2 border-t border-(--color-border) px-4 py-3 text-sm">
                        @foreach(['contract_sent', 'contract_signed', 'mail_envoye', 'guide_sent', 'brings_linen', 'does_cleaning', 'marie_notified', 'concierge_notified', 'codes_sent'] as $field)
                            @if($rental->type === 'exchange' && in_array($field, ['contract_sent', 'contract_signed'], true))
                                @continue
                            @endif
                            <div class="grid min-w-0 grid-cols-[minmax(0,1fr)_2.75rem] items-center justify-self-start gap-1 w-full max-w-56 [&[hidden]]:hidden!" @if($field === 'concierge_notified') data-rental-concierge @if($rental->does_cleaning) hidden @endif @endif>
                                <dt class="min-w-0 font-semibold" id="rental-{{ $rental->id }}-{{ $field }}">{{ __('rentals.fields.'.$field) }}</dt>
                                <dd class="m-0">
                                    <form method="post" action="{{ route('admin.rentals.checklist', [$rental, $field]) }}" data-rental-checklist="{{ $field }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="value" value="{{ $rental->$field ? '0' : '1' }}">
                                        <button type="submit" class="media-button mt-0! w-full min-h-0! px-1! py-0.5! text-sm! text-white! data-[checked=true]:bg-green-700! data-[checked=false]:bg-red-700!" data-checked="{{ $rental->$field ? 'true' : 'false' }}"
                                            aria-describedby="rental-{{ $rental->id }}-{{ $field }}">{{ $rental->$field ? __('users.yes') : __('users.no') }}</button>
                                        <span role="alert" hidden>{{ __('rentals.checklist_update_failed') }}</span>
                                    </form>
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @endunless
                <details id="rental-remarks-{{ $rental->id }}" class="border-t border-(--color-border) px-4 py-2 text-sm" @if($remarksError) open @endif>
                    <summary class="cursor-pointer truncate">
                        @if(filled($rental->remarks))
                            <span class="font-semibold">{{ __('rentals.fields.remarks') }}</span>
                            <span class="ms-1">{{ $rental->remarks }}</span>
                        @else
                            <span class="font-semibold">{{ __('rentals.add_remarks') }}</span>
                        @endif
                    </summary>
                    <form method="post" action="{{ route('admin.rentals.remarks', $rental) }}" class="mt-2 grid gap-2">
                        @csrf
                        @method('PATCH')
                        <textarea name="remarks" rows="2" maxlength="10000" aria-label="{{ __('rentals.fields.remarks') }}">{{ is_string($remarksInput) ? $remarksInput : $rental->remarks }}</textarea>
                        @if($remarksError)
                            <span role="alert" class="text-red-700">{{ $remarksError }}</span>
                        @endif
                        <button type="submit" class="media-button justify-self-start">{{ __('notes.save') }}</button>
                    </form>
                </details>
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
