@php
    $isDashboard = request()->routeIs('admin');
    $icons = [
        'admin.stay' => 'pages-locataires',
        'admin.users' => 'utilisateurs',
        'admin.photos' => 'photos',
        'admin.files' => 'fichiers',
        'admin.notes' => 'notes',
        'admin.annuaire' => 'annuaire',
    ];
@endphp
<nav class="{{ $isDashboard ? 'admin-tiles' : 'admin-menu' }}" aria-label="{{ __('admin.navigation') }}">
    @foreach($icons as $route => $icon)
        <a href="{{ route($route) }}"
           @if(request()->routeIs($route, $route.'.*')) aria-current="page" @endif>
            @if($isDashboard)
                <img class="size-12 shrink-0" src="{{ asset('icones/'.$icon.'.svg') }}" alt="" width="48" height="48">
            @endif
            {{ __($route) }}
        </a>
    @endforeach
</nav>
