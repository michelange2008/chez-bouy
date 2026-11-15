@extends('layout')
@section('title', __('site.stay'))
@section('content')
    @include('partials.language-switcher', ['inStay' => true])
    <h1>{{ __('site.welcome') }},</h1>
    <p>{{ __('site.stay_intro') }}</p>
    <nav class="stay-menu" aria-label="{{ __('stay.summary') }}">
        @foreach(['arrivee', 'installation', 'appartement', 'depart', 'chamonix'] as $page)
            <a href="{{ route('stay.'.$page) }}">
                <span>{{ __('stay.'.$page) }}</span>
                <svg class="size-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 12h16m-7-7 7 7-7 7"/>
                </svg>
            </a>
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
