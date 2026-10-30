@extends('layout')

@section('content')
    <h1>{{ __('site.admin') }}</h1>
    @include('admin.partials.menu')
    <section class="admin-content">
        @yield('admin-content')
    </section>
@endsection
