<nav class="admin-menu" aria-label="{{ __('admin.navigation') }}">
    @foreach(['admin.users', 'admin.photos', 'admin.files'] as $route)
        <a href="{{ route($route) }}"
           @if(request()->routeIs($route, $route.'.*')) aria-current="page" @endif>
            {{ __($route) }}
        </a>
    @endforeach
</nav>
