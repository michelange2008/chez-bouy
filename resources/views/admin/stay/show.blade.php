@extends('admin.layout')
@section('title', __('stay.'.$page))
@section('admin-content')
<div class="users-toolbar">
    <h2>{{ __('stay.'.$page) }}</h2>
    <div class="flex flex-wrap gap-3">
        <a class="media-button" href="{{ route('admin.stay') }}">{{ __('sections.back') }}</a>
        <a class="media-button" href="{{ route($page === 'home' ? 'home' : 'stay.'.$page) }}">{{ __('sections.view') }}</a>
        <a class="media-button" href="{{ route('admin.stay.create', $page) }}">{{ __('sections.add') }}</a>
    </div>
</div>
<div class="media-list">
    @forelse($sections as $section)
        <article class="media-panel">
            <span class="media-badge">{{ $section->published ? __('sections.published') : __('sections.draft') }}</span>
            <h3>{{ $loop->iteration }}. {{ $section->translatedContent()['title'] ?: __('sections.untitled') }}</h3>
            <p class="my-3 whitespace-pre-line break-words">{{ \Illuminate\Support\Str::limit($section->translatedContent()['body'], 180) }}</p>
            <div class="flex flex-wrap gap-3">
                @foreach(config('languages.supported') as $locale => $label)
                <div><p>{{ $label }}</p><div class="flex flex-wrap gap-3">
                @foreach($section->photos->where('pivot.locale', $locale) as $photo)
                    <img class="h-20 w-28 rounded object-cover" src="{{ route('admin.photos.image', ['photo' => $photo, 'thumbnail' => 1]) }}" alt="{{ $photo->translatedTitle() }}" loading="lazy" width="112" height="80">
                @endforeach
                </div></div>
                @endforeach
            </div>
            <div class="section-actions mt-4 flex flex-wrap items-start gap-2 sm:gap-3">
                <a class="media-button" href="{{ route('admin.stay.edit', $section) }}">
                    <svg class="size-6 sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 3 5 5-12 12H4v-5L16 3Z M13 6l5 5"/></svg>
                    <span class="max-sm:sr-only">{{ __('sections.edit') }}</span>
                </a>
                <form method="post" action="{{ route('admin.stay.move', $section) }}">
                    @csrf @method('PATCH')
                    <button class="media-button !mt-0" name="direction" value="up" @disabled($loop->first)>
                        <svg class="size-6 sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20V4m-7 7 7-7 7 7"/></svg>
                        <span class="max-sm:sr-only">{{ __('sections.up') }}</span>
                    </button>
                    <button class="media-button !mt-0" name="direction" value="down" @disabled($loop->last)>
                        <svg class="size-6 sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v16m-7-7 7 7 7-7"/></svg>
                        <span class="max-sm:sr-only">{{ __('sections.down') }}</span>
                    </button>
                </form>
                <form method="post" action="{{ route('admin.stay.publication', $section) }}">
                    @csrf @method('PATCH')
                    <button class="media-button !mt-0" name="published" value="{{ $section->published ? '0' : '1' }}">
                        <svg class="size-6 sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z {{ $section->published ? 'M3 3l18 18' : '' }}"/></svg>
                        <span class="max-sm:sr-only">{{ $section->published ? __('sections.unpublish_action') : __('sections.publish_action') }}</span>
                    </button>
                </form>
                <details class="media-delete !mt-0 ms-auto text-end">
                    <summary class="media-button media-button-danger">
                        <svg class="size-6 sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18 M9 6V3h6v3 M5 6l1 15h12l1-15 M10 10v7 M14 10v7"/></svg>
                        <span class="max-sm:sr-only">{{ __('sections.delete') }}</span>
                    </summary>
                    <p>{{ __('sections.confirm') }}</p>
                    <form method="post" action="{{ route('admin.stay.destroy', $section) }}">
                        @csrf @method('DELETE')
                        <button class="media-button media-button-danger">{{ __('sections.delete') }}</button>
                    </form>
                </details>
            </div>
        </article>
    @empty
        <p>{{ __('sections.empty') }}</p>
    @endforelse
</div>
@endsection
