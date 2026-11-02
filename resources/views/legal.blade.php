@extends('layout')
@section('title', __('legal.title'))
@section('content')
    @include('partials.language-switcher')
    <article class="legal-page">
        <h1>{{ __('legal.title') }}</h1>
        <p>{{ __('legal.intro') }}</p>
        @foreach(['editor', 'host'] as $section)
            <section aria-labelledby="legal-{{ $section }}">
                <h2 id="legal-{{ $section }}">{{ __('legal.'.$section) }}</h2>
                <dl class="legal-details">
                    @foreach(config('legal.'.$section) as $field => $value)
                        <div>
                            <dt>{{ __('legal.fields.'.$field) }}</dt>
                            <dd>{{ $value ?: __('legal.pending') }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach
        <p><a class="button" href="{{ route('home') }}">{{ __('legal.back') }}</a></p>
    </article>
@endsection
