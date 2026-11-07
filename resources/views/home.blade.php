@extends('layout')
@inject('markdown', 'App\StayMarkdown')
@section('content')
    @include('partials.language-switcher')
    <section class="home-heading">
        <p>{{ __('site.home_eyebrow') }}</p>
        <h1>{{ __('site.home_title') }} {{ __('site.home_subtitle') }}</h1>
    </section>
    @if($photos->isNotEmpty())
        <section id="photos" class="home-gallery" aria-labelledby="photos-title" data-home-gallery>
            <div class="home-gallery-heading">
                <h2 id="photos-title">{{ __('site.photos_title') }}</h2>
                @if(count($availablePhotoIds))
                    <button type="button" class="media-button" data-gallery-open hidden>{{ __('site.all_photos', ['count' => count($availablePhotoIds)]) }}</button>
                @endif
            </div>
            <div class="home-mosaic" data-count="{{ min($photos->count(), 5) }}">
                @foreach ($photos as $photo)
                    <figure>
                        @if (in_array($photo->id, $availablePhotoIds, true))
                            <a href="{{ route('home.photos.image', $photo) }}" data-gallery-photo data-caption="{{ $photo->translatedTitle() }}" aria-label="{{ __('photos.enlarge') }} : {{ $photo->translatedTitle() }}">
                                <img src="{{ route('home.photos.image', $photo) }}" alt="{{ $photo->translatedTitle() }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" width="800" height="600">
                            </a>
                        @else
                            <div class="placeholder">{{ __('site.photo_missing') }}</div>
                        @endif
                        <figcaption>{{ $photo->translatedTitle() }}</figcaption>
                    </figure>
                @endforeach
            </div>
            <dialog class="photo-lightbox home-gallery-dialog" data-gallery-dialog aria-labelledby="gallery-caption">
                <div class="home-gallery-heading">
                    <p data-gallery-counter aria-live="polite"></p>
                    <button class="media-button" type="button" data-gallery-close autofocus>{{ __('photos.close') }}</button>
                </div>
                <p class="error" data-gallery-error role="status" hidden>{{ __('photos.preview_failed') }}</p>
                <img class="photo-lightbox-image" data-gallery-image alt="">
                <div class="home-gallery-heading">
                    <button class="media-button" type="button" data-gallery-previous aria-label="{{ __('site.previous_photo') }}">←</button>
                    <p id="gallery-caption" data-gallery-caption></p>
                    <button class="media-button" type="button" data-gallery-next aria-label="{{ __('site.next_photo') }}">→</button>
                </div>
                <div class="home-gallery-thumbnails" data-gallery-thumbnails aria-label="{{ __('site.photos_title') }}"></div>
            </dialog>
        </section>
    @endif
    <ul class="home-apartment-facts">
        <li>
            <svg aria-hidden="true" viewBox="0 0 12 40" fill="none" stroke="currentColor">
                <rect x="1" y="1" width="10" height="38"/>
                <path d="M11 5H7m4 4H9m2 4H7m4 4H9m2 4H7m4 4H9m2 4H7m4 4H9m2 4H7"/>
            </svg>
            <span>50 m²</span>
        </li>
        <li>{{ __('site.apartment_rooms') }}</li>
        <li class="home-location-fact">
            <svg aria-hidden="true" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="22" cy="5" r="2.8" fill="currentColor" stroke="none"/>
                <path d="m19 10-6 7 5 4-6 6m7-17 2 5 6 2M18 9l-7 2-3 6" stroke-width="3.5"/>
                <path d="m2 21 7-4m11 5 7-5M3 24l21 7q3 1 5-1" stroke-width="1.8"/>
            </svg>
            <span>{{ __('site.near_slopes') }}</span>
        </li>
        <li class="home-location-fact">
            <svg aria-hidden="true" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.8">
                <path d="m2 7 9 3 9-3 10 3v19l-10-3-9 3-9-3Zm9 3v19m9-15v12"/>
                <path d="M27 6c0 3-4 8-4 8s-4-5-4-8a4 4 0 0 1 8 0Z" fill="white"/>
                <circle cx="23" cy="6" r="1" fill="currentColor"/>
                <path d="M6 17q8-5 8 2t8-3" stroke-dasharray="2 2" stroke-linecap="round"/>
            </svg>
            <span>{{ __('site.near_town_centre') }}</span>
        </li>
        <li>
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                <path d="M2 8a15 15 0 0 1 20 0M5 12a10.5 10.5 0 0 1 14 0M8.5 16a5.25 5.25 0 0 1 7 0"/>
                <circle cx="12" cy="20" r="1.25"/>
            </svg>
            <span>WiFi</span>
        </li>
    </ul>
    <div class="home-text-sections">
        @forelse($sections as $section)
            @php($content = $section->translatedContent())
            <section>
                @if($content['title'])<h2>{{ $content['title'] }}</h2>@endif
                <div class="stay-markdown">{!! $markdown->render($content['body']) !!}</div>
            </section>
        @empty
            <section>
                <h2>{{ __('site.home_intro_title') }}</h2>
                <p>{{ __('site.home_intro') }}</p>
            </section>
        @endforelse
        <section>
            <p>{{ __('site.home_login_intro') }}</p>
            <a class="button" href="{{ route('login') }}">{{ __('site.view_stay') }}</a>
        </section>
    </div>
@endsection
