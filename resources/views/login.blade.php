@extends('layout')
@section('title', 'Connexion')
@section('content')
<section class="panel">
    <h2>Votre espace privé</h2>
    <p>Saisissez l’adresse e-mail communiquée pour votre séjour. Vous recevrez un code de connexion.</p>
    <form action="{{ route('code.send') }}" method="post">
        @csrf
        <label for="email">Adresse e-mail</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="254">
        <button>Recevoir mon code</button>
    </form>
</section>
@endsection
