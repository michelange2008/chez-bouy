@extends('admin.layout')
@section('title', $section->exists ? __('sections.edit') : __('sections.add'))
@section('admin-content')
<div class="users-toolbar">
    <h2>{{ __('stay.'.$section->page) }} — {{ $section->exists ? __('sections.edit') : __('sections.add') }}</h2>
    <a class="media-button" href="{{ route('admin.stay.show', $section->page) }}">{{ __('sections.back') }}</a>
</div>
<form method="post" action="{{ $section->exists ? route('admin.stay.update', $section) : route('admin.stay.store', $section->page) }}" class="media-form" data-section-form>
    @csrf
    @if($section->exists) @method('PATCH') @endif
    <section class="media-panel" data-language-panels>
        <div class="flex flex-wrap gap-3" data-language-tabs hidden>
            @foreach(config('languages.supported') as $locale => $label)
                <button type="button" class="media-button" data-language="{{ $locale }}" aria-controls="section-language-{{ $locale }}">{{ $label }}</button>
            @endforeach
        </div>
        @foreach(config('languages.supported') as $locale => $label)
            <fieldset class="media-translation" id="section-language-{{ $locale }}" data-language-panel="{{ $locale }}">
                <legend>{{ $label }}</legend>
                <label for="section-title-{{ $locale }}">{{ __('sections.title') }}</label>
                <input id="section-title-{{ $locale }}" name="translations[{{ $locale }}][title]" lang="{{ $locale }}" maxlength="255" value="{{ old('translations.'.$locale.'.title', $section->translations[$locale]['title'] ?? '') }}">
                <label for="section-body-{{ $locale }}">{{ __('sections.body') }}{{ $locale === 'fr' ? ' *' : '' }}</label>
                <textarea id="section-body-{{ $locale }}" name="translations[{{ $locale }}][body]" lang="{{ $locale }}" rows="10" maxlength="50000" @required($locale === 'fr')>{{ old('translations.'.$locale.'.body', $section->translations[$locale]['body'] ?? '') }}</textarea>
            </fieldset>
        @endforeach
        <p class="media-help">{{ __('sections.translation_help') }}</p>
    </section>
    <section class="media-panel mt-6">
        <h3>{{ __('sections.photos') }}</h3>
        <p class="media-help">{{ __('sections.photo_help') }}</p>
        <button class="media-button" name="action" value="add_photo">{{ __('sections.upload') }}</button>
        @php
            $selectedPhotos = old('photos', session()->hasOldInput() ? [] : $section->photos->modelKeys());
            $selectedIds = array_map('intval', is_array($selectedPhotos) ? $selectedPhotos : []);
            $orderedPhotos = collect($selectedIds)->map(fn ($id) => $photos->firstWhere('id', $id))->filter()
                ->concat($photos->whereNotIn('id', $selectedIds));
        @endphp
        <div class="mt-4 grid gap-4" data-section-photos>
            @forelse($orderedPhotos as $photo)
                <div class="flex flex-wrap items-center gap-4 rounded border border-(--color-border) p-3" data-section-photo>
                    <label class="media-checkbox !mt-0 min-w-0 flex-1">
                        <input type="checkbox" name="photos[]" value="{{ $photo->id }}" @checked(in_array($photo->id, $selectedIds, true))>
                        <img class="h-20 w-28 rounded object-cover" src="{{ route('admin.photos.image', ['photo' => $photo, 'thumbnail' => 1]) }}" alt="" width="112" height="80" loading="lazy">
                        <span class="break-words">{{ $photo->translatedTitle() }} @unless($photo->published) ({{ __('sections.draft') }}) @endunless</span>
                    </label>
                    <div class="flex gap-2" data-photo-controls hidden>
                        <button class="media-button !mt-0" type="button" data-photo-move="up">{{ __('sections.up') }}</button>
                        <button class="media-button !mt-0" type="button" data-photo-move="down">{{ __('sections.down') }}</button>
                    </div>
                </div>
            @empty
                <p>{{ __('sections.no_photos') }}</p>
            @endforelse
        </div>
    </section>
    <section class="media-panel mt-6">
        <input type="hidden" name="published" value="0">
        <label class="media-checkbox"><input type="checkbox" name="published" value="1" @checked(old('published', $section->published))>{{ __('sections.publish') }}</label>
    </section>
    <div class="users-form-actions">
        <a class="media-button" href="{{ route('admin.stay.show', $section->page) }}">{{ __('sections.cancel') }}</a>
        <button class="media-button">{{ __('sections.save') }}</button>
    </div>
</form>
@endsection
