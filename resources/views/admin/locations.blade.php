@extends('admin.layout')
@section('title', __('admin.locations'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ __('admin.locations') }}</h2><a class="media-button" href="{{ route('admin.photos') }}">{{ __('admin.photos') }}</a></div>
<div class="media-columns">
    <section class="media-panel">
        <h3>{{ $location->exists ? __('photos.edit_location') : __('photos.add_location') }}</h3>
        <form method="post" action="{{ $location->exists ? route('admin.locations.update', $location) : route('admin.locations.store') }}" class="media-form">
            @csrf
            @if($location->exists) @method('PATCH') @endif
            <label for="location-name">{{ __('photos.name') }}</label>
            <input id="location-name" name="name" value="{{ old('name', $location->name) }}" maxlength="255" required>
            <label for="location-code">{{ __('photos.code') }}</label>
            <input id="location-code" name="code" value="{{ old('code', $location->code) }}" maxlength="100" pattern="[a-z][a-z0-9_-]*" aria-describedby="code-help" required>
            <p id="code-help" class="media-help">{{ __('photos.code_help') }}</p>
            <div class="users-form-actions"><a class="media-button" href="{{ route('admin.locations') }}">{{ __('photos.cancel') }}</a><button class="media-button">{{ __('photos.save') }}</button></div>
        </form>
    </section>
    <section class="media-list" aria-label="{{ __('admin.locations') }}">
        @forelse($locations as $item)
            <article class="media-panel">
                <h3>{{ $item->name }}</h3><p><code>{{ $item->code }}</code> · {{ __('photos.photo_count', ['count' => $item->photos_count]) }}</p>
                <div class="users-actions">
                    <a class="media-button" href="{{ route('admin.locations.edit', $item) }}">{{ __('photos.edit') }}</a>
                    <a class="media-button" href="{{ route('admin.photos', ['location' => $item->id]) }}">{{ __('photos.view_photos') }}</a>
                </div>
                <details class="media-delete"><summary class="media-button media-button-danger">{{ __('photos.delete') }}</summary>
                    <p>{{ __('photos.delete_location_help') }}</p>
                    <form method="post" action="{{ route('admin.locations.destroy', $item) }}">@csrf @method('DELETE')<button class="media-button media-button-danger">{{ __('photos.confirm_delete') }} — {{ $item->name }}</button></form>
                </details>
            </article>
        @empty
            <p>{{ __('photos.no_locations') }}</p>
        @endforelse
    </section>
</div>
@endsection
