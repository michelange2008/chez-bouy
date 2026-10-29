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
        @foreach([
    'montagne.jpg' => 'La vue sur les montagnes',
    'residence.png' => 'La résidence — Chez Bouy',
] as $file => $label)
    <figure>
        @if(\Illuminate\Support\Facades\Storage::disk('public')->exists("photos/{$file}"))
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url("photos/{$file}") }}"
                alt="{{ $label }}"
                loading="lazy"
                width="800"
                height="600"
            >
        @else
            <div class="placeholder">{{ __(site.photo_missing) }}</div>
        @endif
        <figcaption>{{ $label }}</figcaption>
    </figure>
@endforeach
    </div>
</section>
@endsection
