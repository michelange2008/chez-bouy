@extends('admin.layout')

@section('title', __('admin.users'))

@section('admin-content')
    <div class="users-toolbar">
        <h2>{{ __('admin.users') }}</h2>
        <button type="button" data-open-user-dialog="create-user" disabled>{{ __('users.add') }}</button>
    </div>
    <noscript>
        <p>{{ __('users.js_required') }}</p>
    </noscript>

    <div class="users-table-scroll" tabindex="0" role="region" aria-label="{{ __('admin.users') }}">
        <table class="users-table">
            <caption class="users-sr-only">{{ __('admin.users') }}</caption>
            <thead>
                <tr>
                    @foreach (['name', 'first_name', 'email', 'role', 'active', 'actions'] as $label)
                        <th scope="col">{{ __('users.' . $label) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->first_name ?? '—' }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role === 'admin' ? __('users.admin') : __('users.tenant') }}</td>
                        <td>{{ $user->is_active ? __('users.yes') : __('users.no') }}</td>
                        <td>
                            <div class="users-actions">
                                <a class="users-icon" href="{{ route('admin.users.edit', $user) }}"
                                    aria-label="{{ __('users.edit') }} — {{ $user->email }}"
                                    title="{{ __('users.edit') }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m16 3 5 5-12 12H4v-5L16 3Z M13 6l5 5" />
                                    </svg>
                                </a>
                                @unless ($user->is(auth()->user()))
                                    <button type="button" class="users-icon users-danger" disabled data-delete-user
                                        data-user-email="{{ $user->email }}"
                                        data-delete-url="{{ route('admin.users.destroy', $user) }}"
                                        aria-label="{{ __('users.delete') }} — {{ $user->email }}"
                                        title="{{ __('users.delete') }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 6h18 M9 6V3h6v3 M5 6l1 15h12l1-15 M10 10v7 M14 10v7" />
                                        </svg>
                                    </button>
                                @else
                                    <button type="button" class="users-icon" disabled
                                        title="{{ __('users.cannot_delete_self') }}"
                                        aria-label="{{ __('users.cannot_delete_self') }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 6h18 M9 6V3h6v3 M5 6l1 15h12l1-15 M10 10v7 M14 10v7" />
                                        </svg>
                                    </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">{{ __('users.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <nav class="users-pagination" aria-label="{{ __('users.pagination') }}">
            @if ($users->previousPageUrl())
                <a href="{{ $users->previousPageUrl() }}" rel="prev">{{ __('users.previous') }}</a>
            @endif
            <span>{{ __('users.page', ['current' => $users->currentPage(), 'last' => $users->lastPage(), 'total' => $users->total()]) }}</span>
            @if ($users->nextPageUrl())
                <a href="{{ $users->nextPageUrl() }}" rel="next">{{ __('users.next') }}</a>
            @endif
        </nav>
    @endif

    <dialog id="create-user" class="users-dialog" aria-labelledby="create-user-title"
        data-reopen="{{ $errors->createUser->any() ? 'true' : 'false' }}">
        <h2 id="create-user-title">{{ __('users.add') }}</h2>
        @if ($errors->getBag('createUser')->any())
            <div role="alert" class="error">
                @foreach ($errors->getBag('createUser')->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if ($errors->createUser->any())
            <div role="alert" class="error">
                @foreach ($errors->createUser->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <form action="{{ route('admin.users.save') }}" method="post" class="users-form">
            @csrf
            <label for="create-email">{{ __('users.email') }}</label>
            <input id="create-email" name="email" type="email" maxlength="254" value="{{ old('email') }}"
                autocomplete="off" required>
            @include('admin.users.fields', ['formId' => 'create', 'editing' => false])
            <div class="users-form-actions">
                <button type="button" class="users-secondary" data-close-user-dialog>{{ __('users.cancel') }}</button>
                <button type="submit">{{ __('users.save') }}</button>
            </div>
        </form>
    </dialog>

    <dialog id="delete-user" class="users-dialog" aria-labelledby="delete-user-title"
        aria-describedby="delete-user-description">
        <h2 id="delete-user-title">{{ __('users.confirm_title') }}</h2>
        <p id="delete-user-description">{{ __('users.confirm_text') }}</p>
        <p><strong data-delete-identity></strong></p>
        <form method="post" data-delete-form>
            @csrf
            @method('DELETE')
            <div class="users-form-actions">
                <button type="button" class="users-secondary" data-close-user-dialog
                    autofocus>{{ __('users.cancel') }}</button>
                <button type="submit" class="users-danger-fill" disabled>{{ __('users.confirm') }}</button>
            </div>
        </form>
    </dialog>
@endsection
