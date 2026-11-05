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
            <section>
                <h2>{{ __('site.login_title') }}</h2>
                <p>{{ __('site.login_intro') }}</p>
                <a class="button" href="{{ route('login') }}">{{ __('site.view_stay') }}</a>
            </section>
        @endforelse
    </div>
@endsection
