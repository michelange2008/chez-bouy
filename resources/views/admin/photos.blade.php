@extends('admin.layout')
@section('title', __('admin.photos'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ __('admin.photos') }}</h2><a class="media-button" href="{{ route('admin.photos.create') }}">{{ __('photos.add') }}</a></div>
<form method="get" action="{{ route('admin.photos') }}" class="media-filter">
    <label for="location-filter">{{ __('photos.filter') }}</label>
    <select id="location-filter" name="location"><option value="">{{ __('photos.all_locations') }}</option>
        @foreach($locations as $location)<option value="{{ $location->id }}" @selected((string) $locationId === (string) $location->id)>{{ $location->name }}</option>@endforeach
    </select>
    <button class="media-button">{{ __('photos.apply') }}</button>
</form>
<div class="media-grid">
    @forelse($photos as $photo)
        <article class="media-card">
            <img src="{{ route('admin.photos.image', ['photo' => $photo, 'thumbnail' => 1]) }}" alt="{{ $photo->translatedTitle() }}" loading="lazy" width="480" height="320">
            <div class="media-card-body">
                <span class="media-badge">{{ $photo->published ? __('photos.published') : __('photos.draft') }}</span>
                <h3>{{ $photo->translatedTitle() }}</h3>
                <ul>
                    @foreach($photo->locations->where('route_name', 'home') as $location)
                        <li>{{ $location->name }} · {{ __('photos.position') }} {{ $location->pivot->position }}</li>
                    @endforeach
                    @foreach($photo->sections as $section)
                        <li><a href="{{ route('admin.stay.edit', ['section' => $section->id, 'section_locale' => $section->pivot->locale]) }}">{{ __('stay.'.$section->page) }} · {{ strtoupper($section->pivot->locale) }}</a></li>
                    @endforeach
                    @if($photo->locations->where('route_name', 'home')->isEmpty() && $photo->sections->isEmpty())
                        <li>{{ __('photos.unassigned') }}</li>
                    @endif
                </ul>
                <a class="media-button" href="{{ route('admin.photos.edit', $photo) }}">{{ __('photos.edit') }}</a>
                <details class="media-delete"><summary class="media-button media-button-danger">{{ __('photos.delete') }}</summary>
                    <p>{{ __('photos.delete_photo_help') }}</p>
                    <form method="post" action="{{ route('admin.photos.destroy', $photo) }}">@csrf @method('DELETE')<button class="media-button media-button-danger">{{ __('photos.confirm_delete') }}</button></form>
                </details>
            </div>
        </article>
    @empty
        <p>{{ __('photos.empty') }}</p>
    @endforelse
</div>
@if($photos->hasPages())
<nav class="users-pagination" aria-label="{{ __('users.pagination') }}">
    @if($photos->previousPageUrl())<a class="media-button" href="{{ $photos->previousPageUrl() }}">{{ __('users.previous') }}</a>@endif
    <span>{{ __('users.page', ['current' => $photos->currentPage(), 'last' => $photos->lastPage(), 'total' => $photos->total()]) }}</span>
    @if($photos->nextPageUrl())<a class="media-button" href="{{ $photos->nextPageUrl() }}">{{ __('users.next') }}</a>@endif
</nav>
@endif
@endsection
