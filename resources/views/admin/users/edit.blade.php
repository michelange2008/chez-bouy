@extends('admin.layout')

@section('title', __('users.edit'))

@section('admin-content')
<h2>{{ __('users.edit') }}</h2>
<form action="{{ route('admin.users.update', $user) }}" method="post" class="users-form users-edit">
    @csrf
    @method('PATCH')
    <label for="edit-email">{{ __('users.email') }}</label>
    <input id="edit-email" type="email" value="{{ $user->email }}" readonly aria-describedby="email-help">
    <p id="email-help">{{ __('users.email_help') }}</p>
    @include('admin.users.fields', ['formId' => 'edit', 'editing' => true])
    <div class="users-form-actions">
        <a href="{{ route('admin.users') }}">{{ __('users.cancel') }}</a>
        <button type="submit">{{ __('users.save') }}</button>
    </div>
</form>
@endsection
