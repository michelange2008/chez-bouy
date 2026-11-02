@extends('layout')
@section('content')
    @include('partials.language-switcher')
    <section class="intro">
        <p>{{ __('site.home_eyebrow') }}</p>
        <h1>{{ __('site.home_title') }}<br>{{ __('site.home_subtitle') }}</h1>
        <p>{{ __('site.home_intro') }}</p>
        <a class="button" href="#photos">{{ __('site.photos_link') }}</a>
    </section>
    <section id="photos" aria-labelledby="photos-title">
        <h2 id="photos-title">{{ __('site.photos_title') }}</h2>
        <div class="gallery">
            @foreach ($photos as $photo)
                <figure>
                    @if (in_array($photo->id, $availablePhotoIds, true))
                        <img src="{{ route('home.photos.image', $photo) }}"
                            alt="{{ $photo->translatedTitle() }}" loading="lazy" width="800" height="600">
                    @else
                        <div class="placeholder">{{ __('site.photo_missing') }}</div>
                    @endif
                    <figcaption>{{ $photo->translatedTitle() }}</figcaption>
                </figure>
            @endforeach
        </div>
    </section>
@endsection
