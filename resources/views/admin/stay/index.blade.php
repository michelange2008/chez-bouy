@extends('admin.layout')
@section('title', __('admin.stay'))
@section('admin-content')
<h2 class="mb-6 text-2xl">{{ __('admin.stay') }}</h2>
<div class="media-grid">
    @foreach($pages as $page)
        <a class="media-panel" href="{{ route('admin.stay.show', $page) }}">{{ __('stay.'.$page) }}</a>
    @endforeach
</div>
@endsection
