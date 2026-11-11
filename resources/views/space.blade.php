@extends('layout')
@section('title', __('site.stay'))
@section('content')
    @include('partials.language-switcher')
    <h1>{{ __('site.welcome') }},</h1>
    <p>{{ __('site.stay_intro') }}</p>
    <nav class="stay-menu" aria-label="{{ __('stay.summary') }}">
        @foreach(['arrivee', 'installation', 'appartement', 'depart', 'chamonix'] as $page)
            <a href="{{ route('stay.'.$page) }}">{{ __('stay.'.$page) }}</a>
        @endforeach
    </nav>
    <div class="stay-guide">
        @if($welcomeGuideAvailable)
            <a class="button" href="{{ route('stay.guide') }}">{{ __('stay.download_guide') }}</a>
        @else
            <p>{{ __('stay.guide_unavailable') }}</p>
        @endif
    </div>
@endsection
