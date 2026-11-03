@extends('admin.layout')
@section('title', __('stay.'.$page))
@section('admin-content')
<div class="users-toolbar">
    <h2>{{ __('stay.'.$page) }}</h2>
    <div class="flex flex-wrap gap-3">
        <a class="media-button" href="{{ route('admin.stay') }}">{{ __('sections.back') }}</a>
        <a class="media-button" href="{{ route('stay.'.$page) }}">{{ __('sections.view') }}</a>
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
                @foreach($section->photos as $photo)
                    <img class="h-20 w-28 rounded object-cover" src="{{ route('admin.photos.image', ['photo' => $photo, 'thumbnail' => 1]) }}" alt="{{ $photo->translatedTitle() }}" loading="lazy" width="112" height="80">
                @endforeach
            </div>
            <div class="mt-4 flex flex-wrap items-start gap-3">
                <a class="media-button" href="{{ route('admin.stay.edit', $section) }}">{{ __('sections.edit') }}</a>
                <form method="post" action="{{ route('admin.stay.move', $section) }}">
                    @csrf @method('PATCH')
                    <button class="media-button !mt-0" name="direction" value="up" @disabled($loop->first)>{{ __('sections.up') }}</button>
                    <button class="media-button !mt-0" name="direction" value="down" @disabled($loop->last)>{{ __('sections.down') }}</button>
                </form>
                <details class="media-delete !mt-0">
                    <summary class="media-button">{{ __('sections.delete') }}</summary>
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
