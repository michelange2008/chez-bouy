@extends('admin.layout')
@section('title', $rental->exists ? __('rentals.edit') : __('rentals.create'))
@section('admin-content')
    <div class="users-toolbar">
        <h2>{{ $rental->exists ? __('rentals.edit') : __('rentals.create') }}</h2>
        <a class="media-button" href="{{ route('admin.rentals') }}">{{ __('rentals.back') }}</a>
    </div>
    <form method="post" action="{{ $rental->exists ? route('admin.rentals.update', $rental) : route('admin.rentals.store') }}" class="media-form grid gap-4" data-rental-form>
        @csrf
        @if($rental->exists) @method('PATCH') @endif
        <section class="media-panel">
            @unless($rental->exists)
                <label for="rental-user">{{ __('rentals.fields.user_id') }} *</label>
                <select id="rental-user" name="user_id" required>
                    <option value="">{{ __('rentals.choose_user') }}</option>
                    <option value="new" @selected(old('user_id') === 'new')>{{ __('users.add') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>{{ $user->fullName() }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                <fieldset class="mt-4" data-rental-new-user>
                    <legend>{{ __('users.add') }}</legend>
                    @foreach(['new_name' => 'text', 'new_first_name' => 'text', 'new_email' => 'email'] as $field => $type)
                        <label for="rental-{{ $field }}">{{ __('rentals.fields.'.$field) }}</label>
                        <input id="rental-{{ $field }}" name="{{ $field }}" type="{{ $type }}" maxlength="{{ $type === 'email' ? 254 : 255 }}" value="{{ old($field) }}" @if($field !== 'new_first_name') data-required-for-new @endif>
                    @endforeach
                </fieldset>
            @endunless
            <div class="grid gap-x-4 sm:grid-cols-2">
                @foreach(['arrival', 'departure'] as $field)
                    <div>
                        <label for="rental-{{ $field }}">{{ __('rentals.fields.'.$field) }} *</label>
                        <input id="rental-{{ $field }}" name="{{ $field }}" type="date" value="{{ old($field, $rental->$field?->toDateString()) }}" required>
                    </div>
                @endforeach
                <div>
                    <label for="rental-type">{{ __('rentals.fields.type') }} *</label>
                    <select id="rental-type" name="type" required>
                        <option value="">{{ __('rentals.choose_type') }}</option>
                        @foreach(\App\Models\Rental::TYPES as $type)
                            <option value="{{ $type }}" @selected(old('type', $rental->type) === $type)>{{ __('rentals.types.'.$type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div data-rental-amount @if(old('type', $rental->type) !== 'rental') hidden @endif>
                    <label for="rental-amount">{{ __('rentals.fields.amount') }} *</label>
                    <input id="rental-amount" name="amount" type="number" min="0.01" max="99999999.99" step="0.01" value="{{ old('amount', $rental->amount) }}">
                </div>
            </div>
        </section>
        <div class="users-form-actions">
            <a class="media-button" href="{{ route('admin.rentals') }}">{{ __('notes.cancel') }}</a>
            <button class="media-button">{{ __('notes.save') }}</button>
        </div>
    </form>
@endsection
