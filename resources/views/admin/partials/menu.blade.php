<nav class="admin-menu" aria-label="{{ __('admin.navigation') }}">
    @foreach(['admin.stay', 'admin.users', 'admin.photos', 'admin.files', 'admin.notes', 'admin.annuaire'] as $route)
        <a href="{{ route($route) }}"
           @if(request()->routeIs($route, $route.'.*')) aria-current="page" @endif>
            {{ __($route) }}
        </a>
    @endforeach
</nav>
