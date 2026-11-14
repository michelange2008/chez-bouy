<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bienvenue') — Chez Bouy</title>
    <link rel="icon" type="image/svg+xml" href="icon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-theme="@yield('theme', 'public')">
<header @auth class="authenticated-header" @endauth>
    <a class="brand" href="{{ route('home') }}">
        <img class="img-brand" src="icon.svg" alt="">
        <strong>Chez Bouy</strong></a>
    <nav aria-label="Navigation principale">
        @auth
            <a href="{{ route('dashboard') }}">
                <img class="size-7 sm:hidden" src="{{ asset('icones/sejour.svg') }}" alt="">
                <span class="max-sm:sr-only">{{ __('site.stay') }}</span>
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin') }}">
                    <img class="size-7 sm:hidden" src="{{ asset('icones/admin.svg') }}" alt="">
                    <span class="max-sm:sr-only">{{ __('site.admin') }}</span>
                </a>
            @endif
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit">
                    <img class="size-7 sm:hidden" src="{{ asset('icones/deconnexion.svg') }}" alt="">
                    <span class="max-sm:sr-only">{{ __('site.logout') }}</span>
                </button>
            </form>
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
@include('partials.footer')
</body>
</html>
