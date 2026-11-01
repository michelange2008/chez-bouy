@extends('admin.layout')
@section('title', $note->exists ? __('notes.edit') : __('notes.add'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ $note->exists ? __('notes.edit') : __('notes.add') }}</h2><a class="media-button" href="{{ route('admin.notes') }}">{{ __('notes.back') }}</a></div>
<form action="{{ $note->exists ? route('admin.notes.update', $note) : route('admin.notes.store') }}" method="post" class="media-form">
    @csrf
    @if($note->exists) @method('PATCH') @endif
    <section class="media-panel">
            <label for="note-title">{{ __('notes.title') }} *</label>
            <input id="note-title" name="title" maxlength="255" value="{{ old('title', $note->title) }}" required>
            <label for="note-description">{{ __('notes.description') }}</label>
            <textarea id="note-description" name="description" rows="4" maxlength="50000">{{ old('description', $note->description) }}</textarea>
        </section>
    <div class="users-form-actions"><a class="media-button" href="{{ route('admin.notes') }}">{{ __('notes.cancel') }}</a><button class="media-button">{{ __('notes.save') }}</button></div>
</form>
@endsection
