@extends('layout')

@inject('markdown', 'App\StayMarkdown')

@section('content')
    @include('partials.language-switcher')
    <h1>@yield('title')</h1>
    <div class="my-8 grid gap-10">
        @foreach($sections as $section)
            @php($content = $section->translatedContent())
            <section class="min-w-0">
                @if($content['title'])<h2 class="mb-4 text-2xl bg-gray-200 p-2">{{ $content['title'] }}</h2>@endif
                <div class="stay-markdown">{!! $markdown->render($content['body']) !!}</div>
                <div class="gallery mt-5 flex flex-wrap gap-3 sm:gap-5">
                    @foreach($section->photos as $photo)
                        <figure class="min-w-0 grow basis-[calc((100%-1.5rem)/3)] wrap-anywhere sm:basis-[calc((100%-2.5rem)/3)]">
                            @if(in_array($photo->id, $availablePhotoIds, true))
                                <img src="{{ route('stay.photos.image', $photo) }}" alt="{{ $photo->translatedTitle() }}" width="800" height="600" loading="lazy">
                            @else
                                <div class="placeholder">{{ __('site.photo_missing') }}</div>
                            @endif
                            <figcaption>{{ $photo->translatedTitle() }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
    @yield('stay-content')
    <a class="button" href="{{ route('dashboard') }}">{{ __('stay.back') }}</a>
@endsection
