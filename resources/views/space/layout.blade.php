@extends('layout')

@section('content')
    @include('partials.language-switcher')
    <h1>@yield('title')</h1>
    @yield('stay-content')
    <a class="button" href="{{ route('dashboard') }}">{{ __('stay.back') }}</a>
@endsection
