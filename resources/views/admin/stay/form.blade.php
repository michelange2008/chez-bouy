@extends('admin.layout')
@section('title', $section->exists ? __('sections.edit') : __('sections.add'))
@section('admin-content')
<div class="users-toolbar">
    <h2>{{ __('stay.'.$section->page) }} — {{ $section->exists ? __('sections.edit') : __('sections.add') }}</h2>
    <a class="media-button" href="{{ route('admin.stay.show', $section->page) }}">{{ __('sections.back') }}</a>
</div>
<form method="post" action="{{ $section->exists ? route('admin.stay.update', $section) : route('admin.stay.store', $section->page) }}" class="media-form" data-section-form data-active-language="{{ old('action', request('section_locale', 'fr')) }}" data-preview-url="{{ route('admin.stay.preview') }}">
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
                <div class="my-3 flex flex-wrap items-center gap-3" data-markdown-toolbar hidden>
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach(['bold', 'italic', 'heading', 'quote', 'link'] as $format)
                            <button type="button" class="media-button !mt-0 !min-h-8 !px-2 !py-1 !text-sm" data-markdown-format="{{ $format }}" aria-controls="section-body-{{ $locale }}" aria-label="{{ __('sections.'.$format) }}" title="{{ __('sections.'.$format) }}">
                                @switch($format)
                                    @case('bold')
                                        <strong>{{ __('sections.'.$format) }}</strong>
                                        @break
                                    @case('italic')
                                        <em>{{ __('sections.'.$format) }}</em>
                                        @break
                                    @case('heading')
                                        <span class="font-serif text-base font-semibold leading-tight">{{ __('sections.'.$format) }}</span>
                                        @break
                                    @case('quote')
                                        <span class="border-l-2 border-current pl-2">{{ __('sections.'.$format) }}</span>
                                        @break
                                    @case('link')
                                        <span class="underline underline-offset-2">{{ __('sections.'.$format) }}</span>
                                        @break
                                @endswitch
                            </button>
                        @endforeach
                    </div>
                    <button type="button" class="media-button !mt-0 ml-auto" data-markdown-preview aria-controls="section-preview-{{ $locale }}">{{ __('sections.preview') }}</button>
                </div>
                <textarea aria-describedby="markdown-help-{{ $locale }}" id="section-body-{{ $locale }}" name="translations[{{ $locale }}][body]" lang="{{ $locale }}" rows="10" maxlength="50000" @required($locale === 'fr')>{{ old('translations.'.$locale.'.body', $section->translations[$locale]['body'] ?? '') }}</textarea>
                <p class="media-help" id="markdown-help-{{ $locale }}">{{ __('sections.markdown_help') }}</p>
                <p data-preview-status role="status" data-loading="{{ __('sections.preview_loading') }}" data-error="{{ __('sections.preview_error') }}" data-stale="{{ __('sections.preview_stale') }}" data-ready="{{ __('sections.preview_ready') }}"></p>
                <div class="stay-markdown rounded border border-(--color-border) p-4" id="section-preview-{{ $locale }}" data-markdown-output lang="{{ $locale }}" hidden></div>
                <div class="mt-6">
                    <h3>{{ __('sections.photos') }}</h3>
                    <p class="media-help">{{ __('sections.photo_help') }}</p>
                    <button class="media-button" name="action" value="{{ $locale }}">{{ __('sections.upload') }}</button>
                    @php
                        $selectedPhotos = old('photos.'.$locale, session()->hasOldInput() ? [] : $section->photos->where('pivot.locale', $locale)->modelKeys());
                        $selectedIds = array_map('intval', is_array($selectedPhotos) ? $selectedPhotos : []);
                        $orderedPhotos = collect($selectedIds)->map(fn ($id) => $photos->firstWhere('id', $id))->filter()
                            ->concat($photos->whereNotIn('id', $selectedIds));
                    @endphp
                    <div class="mt-4 grid gap-4" data-section-photos>
                        @forelse($orderedPhotos as $photo)
                            <div class="flex flex-wrap items-center gap-4 rounded border border-(--color-border) p-3" data-section-photo>
                                <label class="media-checkbox !mt-0 min-w-0 flex-1">
                                    <input type="checkbox" name="photos[{{ $locale }}][]" value="{{ $photo->id }}" @checked(in_array($photo->id, $selectedIds, true))>
                                    <img class="h-20 w-28 rounded object-cover" src="{{ route('admin.photos.image', ['photo' => $photo, 'thumbnail' => 1]) }}" alt="" width="112" height="80" loading="lazy">
                                    <span class="min-w-0 break-words">
                                        {{ $photo->translatedTitle() }} @unless($photo->published) ({{ __('sections.draft') }}) @endunless
                                        <small class="text-xs text-(--color-text-muted)">({{ basename($photo->filename) }})</small>
                                    </span>
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
                </div>
            </fieldset>
        @endforeach
        <p class="media-help">{{ __('sections.translation_help') }}</p>
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
