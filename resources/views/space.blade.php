@extends('layout')
@section('title', 'Mon séjour')
@section('content')
<h1>{{ __('site.welcome') }}, {{ auth()->user()->name }}.</h1>
<p>{{ __('site.stay_intro') }}</p>
@endsection
