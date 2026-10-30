<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bienvenue') — Chez Bouy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header>
    <a href="{{ route('home') }}"><strong>Chez Bouy</strong></a>
    <nav aria-label="Navigation principale">
        @auth
            <a href="{{ route('dashboard') }}">Mon séjour</a>
            @if(auth()->user()->role === 'admin')<a href="{{ route('admin') }}">Administration</a>@endif
            <form action="{{ route('logout') }}" method="post">@csrf<button>Déconnexion</button></form>
        @else
            <a href="{{ route('login') }}">Espace privé</a>
        @endauth
    </nav>
</header>
<main>
    @if(session('status'))<p role="status">{{ session('status') }}</p>@endif
    @if($errors->any())<div class="error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @yield('content')
</main>
<footer>Chez Bouy · {{ __('site.footer') }}</footer>
</body>
</html>
