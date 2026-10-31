@extends('admin.layout')
@section('title', $photo->exists ? __('photos.edit') : __('photos.add'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ $photo->exists ? __('photos.edit') : __('photos.add') }}</h2><a class="media-button" href="{{ route('admin.photos') }}">{{ __('photos.back') }}</a></div>
<form action="{{ $photo->exists ? route('admin.photos.update', $photo) : route('admin.photos.store') }}" method="post" enctype="multipart/form-data" class="media-form"
      data-photo-upload="{{ json_encode(['maxDimension' => config('photos.max_dimension'), 'maxBytes' => config('photos.max_upload_kb') * 1024, 'quality' => config('photos.quality') / 100, 'processing' => __('photos.processing'), 'ready' => __('photos.prepared'), 'failed' => __('photos.preparation_failed')]) }}">
    @csrf
    @if($photo->exists) @method('PATCH') @endif
    <div class="media-columns">
        <section class="media-panel">
            <h3>{{ __('photos.file') }}</h3>
            @if($photo->exists)<img class="media-preview" src="{{ route('admin.photos.image', $photo) }}" alt="{{ $photo->translatedTitle() }}">@endif
            <label for="photo-image">{{ $photo->exists ? __('photos.replace_file') : __('photos.file') }}</label>
            <input id="photo-image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(!$photo->exists) aria-describedby="file-help">
            <p id="file-help" class="media-help">{{ __('photos.file_help') }}</p>
            <p data-photo-status role="status" aria-live="polite"></p>
            <img data-photo-preview class="media-preview" alt="{{ __('photos.preview') }}" hidden>
            <noscript><p class="error">{{ __('photos.no_javascript') }}</p></noscript>
            <input type="hidden" name="published" value="0">
            <label class="media-checkbox"><input type="checkbox" name="published" value="1" @checked(old('published', $photo->published))>{{ __('photos.published') }}</label>
            <h3>{{ __('admin.locations') }}</h3>
            <p class="media-help">{{ __('photos.assignment_help') }}</p>
            @forelse($locations as $index => $location)
                @php
                    $association = $photo->locations->firstWhere('id', $location->id);
                    $oldLocations = old('locations');
                    $selected = $oldLocations !== null ? collect($oldLocations)->firstWhere('id', $location->id) : ($association ? ['id' => $location->id, 'position' => $association->pivot->position] : null);
                @endphp
                <div class="media-location-row">
                    <label class="media-checkbox"><input type="checkbox" name="locations[{{ $index }}][id]" value="{{ $location->id }}" @checked($selected)>{{ $location->name }}</label>
                    <label for="position-{{ $location->id }}">{{ __('photos.position') }}</label>
                    <input id="position-{{ $location->id }}" type="number" min="0" max="2147483647" name="locations[{{ $index }}][position]" value="{{ $selected['position'] ?? 0 }}" required>
                </div>
            @empty
                <p>{{ __('photos.no_locations') }} <a class="media-button" href="{{ route('admin.locations') }}">{{ __('photos.manage_locations') }}</a></p>
            @endforelse
        </section>
        <section class="media-panel">
            <h3>{{ __('photos.translations') }}</h3>
            <p class="media-help">{{ __('photos.translation_help') }}</p>
            @foreach(config('languages.supported') as $locale => $label)
                <fieldset class="media-translation">
                    <legend>{{ $label }}</legend>
                    <label for="title-{{ $locale }}">{{ __('photos.title') }}{{ $locale === 'fr' ? ' *' : '' }}</label>
                    <input id="title-{{ $locale }}" name="translations[{{ $locale }}][title]" lang="{{ $locale }}" maxlength="255" value="{{ old('translations.'.$locale.'.title', $translations->get($locale)?->title) }}" @required($locale === 'fr')>
                    <label for="description-{{ $locale }}">{{ __('photos.description') }}</label>
                    <textarea id="description-{{ $locale }}" name="translations[{{ $locale }}][description]" lang="{{ $locale }}" rows="4" maxlength="50000">{{ old('translations.'.$locale.'.description', $translations->get($locale)?->description) }}</textarea>
                </fieldset>
            @endforeach
        </section>
    </div>
    <div class="users-form-actions"><a class="media-button" href="{{ route('admin.photos') }}">{{ __('photos.cancel') }}</a><button class="media-button" data-photo-submit>{{ __('photos.save') }}</button></div>
</form>
@endsection
