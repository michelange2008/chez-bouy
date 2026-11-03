@extends('admin.layout')
@section('title', $photo->exists ? __('photos.edit') : __('photos.add'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ $photo->exists ? __('photos.edit') : __('photos.add') }}</h2><a class="media-button" href="{{ (old('section_id', $sectionId) ? route('admin.stay.edit', old('section_id', $sectionId)) : route('admin.photos')) }}">{{ __('photos.back') }}</a></div>
<form action="{{ $photo->exists ? route('admin.photos.update', $photo) : route('admin.photos.store') }}" method="post" enctype="multipart/form-data" class="media-form"
      data-photo-upload="{{ json_encode(['maxDimension' => config('photos.max_dimension'), 'maxBytes' => config('photos.max_upload_kb') * 1024, 'quality' => config('photos.quality') / 100, 'processing' => __('photos.processing'), 'ready' => __('photos.prepared'), 'failed' => __('photos.preparation_failed')]) }}">
    @csrf
    @if(old('section_id', $sectionId))
        <input type="hidden" name="section_id" value="{{ old('section_id', $sectionId) }}">
    @endif
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
            <label for="photo-location">{{ __('photos.filter') }}</label>
            <select id="photo-location" name="location_id">
                <option value="">{{ __('photos.unassigned') }}</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" @selected((string) old('location_id', $photo->locations->first()?->id) === (string) $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
            <label for="photo-position">{{ __('photos.position') }}</label>
            <input id="photo-position" name="position" type="number" min="0" max="2147483647" value="{{ old('position', $photo->locations->first()?->pivot->position ?? 0) }}" aria-describedby="position-help">
            <p id="position-help" class="media-help">{{ __('photos.position_help') }}</p>
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
    <div class="users-form-actions"><a class="media-button" href="{{ (old('section_id', $sectionId) ? route('admin.stay.edit', old('section_id', $sectionId)) : route('admin.photos')) }}">{{ __('photos.cancel') }}</a><button class="media-button" data-photo-submit>{{ __('photos.save') }}</button></div>
</form>
@endsection
