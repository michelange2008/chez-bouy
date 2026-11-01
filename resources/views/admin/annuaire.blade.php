@extends('admin.layout')
@section('title', __('admin.annuaire'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ __('admin.annuaire') }}</h2><a class="media-button" href="{{ route('admin.annuaire.create') }}">{{ __('annuaire.add') }}</a></div>
<div class="media-grid">
    @forelse($contacts as $contact)
        <article class="media-card">
            <div class="annuaire-heading">
                <h3>{{ $contact->nom }}</h3>
                <div class="note-actions annuaire-actions">
                <a class="media-button" href="{{ route('admin.annuaire.edit', $contact) }}">{{ __('annuaire.modify') }}</a>
                <details class="media-delete"><summary class="media-button media-button-danger">{{ __('annuaire.delete') }}</summary>
                    <p>{{ __('annuaire.delete_help') }}</p>
                    <form method="post" action="{{ route('admin.annuaire.destroy', $contact) }}">@csrf @method('DELETE')<button class="media-button media-button-danger">{{ __('annuaire.confirm_delete') }}</button></form>
                </details>
                </div>
            </div>
            <div class="media-card-body">
                <table class="annuaire-table" aria-label="{{ $contact->nom }}">
                    <tbody>
                        @foreach(['fonction', 'telephone', 'mail', 'adresse_postale', 'observations'] as $field)
                            @if($contact->$field)
                                <tr>
                                    <th scope="row">{{ __('annuaire.'.$field) }}</th>
                                    <td>@if($field === 'mail')<a href="mailto:{{ $contact->mail }}">{{ $contact->mail }}</a>@elseif($field === 'telephone'){{ $contact->formattedTelephone() }}@else{{ $contact->$field }}@endif</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    @empty
        <p>{{ __('annuaire.empty') }}</p>
    @endforelse
</div>
@if($contacts->hasPages())
<nav class="users-pagination" aria-label="{{ __('users.pagination') }}">
    @if($contacts->previousPageUrl())<a class="media-button" href="{{ $contacts->previousPageUrl() }}">{{ __('users.previous') }}</a>@endif
    <span>{{ __('users.page', ['current' => $contacts->currentPage(), 'last' => $contacts->lastPage(), 'total' => $contacts->total()]) }}</span>
    @if($contacts->nextPageUrl())<a class="media-button" href="{{ $contacts->nextPageUrl() }}">{{ __('users.next') }}</a>@endif
</nav>
@endif
@endsection
