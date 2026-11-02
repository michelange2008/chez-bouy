@extends('admin.layout')
@section('title', __('admin.files'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ __('admin.files') }}</h2><a class="media-button" href="{{ route('admin.files.create') }}">{{ __('files.add') }}</a></div>
<div class="media-grid">
    @forelse($files as $file)
        <article class="media-card">
            <div class="media-card-body">
                <h3>{{ $file->title }}</h3>
                <span class="media-badge">{{ config('languages.supported')[$file->locale] ?? $file->locale }}</span>
                @if($file->purpose === \App\Models\File::WELCOME_GUIDE)
                    <span class="media-badge">{{ __('files.welcome_guide') }}</span>
                @endif
                <p>{{ $file->filename }}</p>
                @if($file->description)<p>{{ $file->description }}</p>@endif
                <a class="media-button" href="{{ route('admin.files.download', $file) }}">{{ __('files.download') }}</a>
                <a class="media-button" href="{{ route('admin.files.edit', $file) }}">{{ __('files.edit') }}</a>
                <details class="media-delete"><summary class="media-button media-button-danger">{{ __('files.delete') }}</summary>
                    <p>{{ __('files.delete_file_help') }}</p>
                    <form method="post" action="{{ route('admin.files.destroy', $file) }}">@csrf @method('DELETE')<button class="media-button media-button-danger">{{ __('files.confirm_delete') }}</button></form>
                </details>
            </div>
        </article>
    @empty
        <p>{{ __('files.empty') }}</p>
    @endforelse
</div>
@if($files->hasPages())
<nav class="users-pagination" aria-label="{{ __('users.pagination') }}">
    @if($files->previousPageUrl())<a class="media-button" href="{{ $files->previousPageUrl() }}">{{ __('users.previous') }}</a>@endif
    <span>{{ __('users.page', ['current' => $files->currentPage(), 'last' => $files->lastPage(), 'total' => $files->total()]) }}</span>
    @if($files->nextPageUrl())<a class="media-button" href="{{ $files->nextPageUrl() }}">{{ __('users.next') }}</a>@endif
</nav>
@endif
@endsection
