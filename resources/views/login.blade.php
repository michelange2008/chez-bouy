@extends('layout')
@section('title', 'Connexion')
@section('content')
<section class="panel">
    <h2>{{__('site.login_title')}}
    <p>{{ __('site.login_intro') }}</p>
    <form action="{{ route('code.send') }}" method="post">
        @csrf
        <label for="email">{{ __('site.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="254">
        <button>{{ __('site.send_code') }}</button>
    </form>
</section>
@endsection
