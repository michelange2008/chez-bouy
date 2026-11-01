@extends('admin.layout')
@section('title', __('admin.notes'))
@section('admin-content')
    <div class="users-toolbar">
        <h2>{{ __('admin.notes') }}</h2><a class="media-button"
            href="{{ route('admin.notes.create') }}">{{ __('notes.add') }}</a>
    </div>
    <div class="media-grid">
        @forelse($notes as $note)
            <article class="media-card">
                <div class="note-heading">
                    <h3>{{ $note->title }} (<time
                            datetime="{{ $note->updated_at->format('Y-m-d') }}">{{ $note->updated_at->format('d/m/Y') }}</time> - 
                        {{ $note->author ? trim($note->author->first_name . ' ' . $note->author->name) : __('notes.unknown_author') }})
                    </h3>
                    <div class="note-actions">
                        <a class="media-button" href="{{ route('admin.notes.edit', $note) }}">{{ __('notes.modify') }}</a>
                        <details class="media-delete">
                            <summary class="media-button media-button-danger">{{ __('notes.delete') }}</summary>
                            <p>{{ __('notes.delete_help') }}</p>
                            <form method="post" action="{{ route('admin.notes.destroy', $note) }}">@csrf
                                @method('DELETE')<button
                                    class="media-button media-button-danger">{{ __('notes.confirm_delete') }}</button>
                            </form>
                        </details>
                    </div>
                </div>
                @if ($note->description)
                    <div class="media-card-body">
                        <p>{{ $note->description }}</p>
                    </div>
                @endif
            </article>
        @empty
            <p>{{ __('notes.empty') }}</p>
        @endforelse
    </div>
    @if ($notes->hasPages())
        <nav class="users-pagination" aria-label="{{ __('users.pagination') }}">
            @if ($notes->previousPageUrl())
                <a class="media-button" href="{{ $notes->previousPageUrl() }}">{{ __('users.previous') }}</a>
            @endif
            <span>{{ __('users.page', ['current' => $notes->currentPage(), 'last' => $notes->lastPage(), 'total' => $notes->total()]) }}</span>
            @if ($notes->nextPageUrl())
                <a class="media-button" href="{{ $notes->nextPageUrl() }}">{{ __('users.next') }}</a>
            @endif
        </nav>
    @endif
@endsection
