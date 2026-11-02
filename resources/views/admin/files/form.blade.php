@extends('admin.layout')
@section('title', $file->exists ? __('files.edit') : __('files.add'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ $file->exists ? __('files.edit') : __('files.add') }}</h2><a class="media-button" href="{{ route('admin.files') }}">{{ __('files.back') }}</a></div>
<form action="{{ $file->exists ? route('admin.files.update', $file) : route('admin.files.store') }}" method="post" enctype="multipart/form-data" class="media-form">
    @csrf
    @if($file->exists) @method('PATCH') @endif
    <div class="media-columns">
        <section class="media-panel">
            <h3>{{ __('files.file') }}</h3>
            @if($file->exists)<p>{{ $file->filename }}</p>@endif
            <label for="file-upload">{{ $file->exists ? __('files.replace_file') : __('files.file') }}</label>
            <input id="file-upload" name="upload" type="file" @required(!$file->exists) aria-describedby="file-help">
            <p id="file-help" class="media-help">{{ __('files.file_help') }}</p>
        </section>
        <section class="media-panel">
            <label for="file-locale">{{ __('files.language') }}</label>
            <select id="file-locale" name="locale" required>
                @foreach(config('languages.supported') as $locale => $label)
                    <option value="{{ $locale }}" @selected(old('locale', $file->locale ?? 'fr') === $locale)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="hidden" name="welcome_guide" value="0">
            <label class="media-checkbox" for="file-welcome-guide">
                <input id="file-welcome-guide" type="checkbox" name="welcome_guide" value="1" @checked(old('welcome_guide', $file->purpose === \App\Models\File::WELCOME_GUIDE)) aria-describedby="welcome-guide-help">
                {{ __('files.welcome_guide') }}
            </label>
            <p id="welcome-guide-help" class="media-help">{{ __('files.welcome_guide_help') }}</p>
            <label for="file-title">{{ __('files.title') }} *</label>
            <input id="file-title" name="title" maxlength="255" value="{{ old('title', $file->title) }}" required>
            <label for="file-description">{{ __('files.description') }}</label>
            <textarea id="file-description" name="description" rows="4" maxlength="50000">{{ old('description', $file->description) }}</textarea>
        </section>
    </div>
    <div class="users-form-actions"><a class="media-button" href="{{ route('admin.files') }}">{{ __('files.cancel') }}</a><button class="media-button">{{ __('files.save') }}</button></div>
</form>
@endsection
