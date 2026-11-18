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
            <div class="media-card-body grid gap-1.5 p-3! text-xs">
                <h3 class="m-0! text-sm font-semibold">{{ $photo->translatedTitle() }}</h3>
                <div class="flex min-w-0 items-center gap-2">
                    @if($photo->original_filename)
                        <span class="min-w-0 truncate text-(--color-text-muted)" title="{{ $photo->original_filename }}">{{ $photo->original_filename }}</span>
                    @endif
                    <span class="media-badge mb-0! shrink-0 px-2! py-0.5! text-xs!">{{ $photo->published ? __('photos.published') : __('photos.draft') }}</span>
                </div>
                <ul class="mb-0! ps-4!">
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
                <div class="flex items-start gap-1.5">
                    <a class="media-button size-8 min-h-0! shrink-0 p-0!" href="{{ route('admin.photos.edit', $photo) }}" title="{{ __('photos.edit') }}" aria-label="{{ __('photos.edit') }} — {{ $photo->translatedTitle() }}">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 3 5 5-12 12H4v-5L16 3Z M13 6l5 5"/></svg>
                    </a>
                    <details class="media-delete mt-0! min-w-0">
                        <summary class="media-button media-button-danger size-8 min-h-0! p-0!" title="{{ __('photos.delete') }}" aria-label="{{ __('photos.delete') }} — {{ $photo->translatedTitle() }}">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18 M9 6V3h6v3 M5 6l1 15h12l1-15 M10 10v7 M14 10v7"/></svg>
                        </summary>
                        <p>{{ __('photos.delete_photo_help') }}</p>
                        <form method="post" action="{{ route('admin.photos.destroy', $photo) }}">
                            @csrf
                            @method('DELETE')
                            <button class="media-button media-button-danger text-xs!">{{ __('photos.confirm_delete') }}</button>
                        </form>
                    </details>
                </div>
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
