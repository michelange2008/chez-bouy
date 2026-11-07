@extends('layout')
@section('title', __($messageSent ? 'site.contact_sent' : 'site.contact_title'))
@section('content')
    @include('partials.language-switcher')
    <section @class(['panel', 'contact-panel', 'contact-confirmation' => $messageSent])>
        <img class="contact-photo" src="{{ asset('aiguilles_de_chamonix.webp') }}" alt="" width="800" height="253">
        <h1>@yield('title')</h1>
        @if($messageSent)
            <a class="button" href="{{ route('home') }}">{{ __('site.back_home') }}</a>
        @else
            <form action="{{ route('contact.store') }}" method="post">
                @csrf
                <label for="email">{{ __('site.contact_email') }}</label>
                <input id="email" type="email" name="email" value="{{ is_string(old('email')) ? old('email') : '' }}" autocomplete="email" maxlength="254" required>
                <label for="message">{{ __('site.contact_message') }}</label>
                <textarea id="message" name="message" rows="7" maxlength="5000" required>{{ is_string(old('message')) ? old('message') : '' }}</textarea>
                <button type="submit">{{ __('site.contact_send') }}</button>
            </form>
        @endif
    </section>
@endsection
