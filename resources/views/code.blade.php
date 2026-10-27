@extends('layout')
@section('title', 'Votre code')
@section('content')
<section class="panel">
    <h2>Consultez vos e-mails</h2>
    <p>Le code est valable 10 minutes. Chaque nouveau code remplace le précédent.</p>
    <form action="{{ route('code.verify') }}" method="post">
        @csrf
        <label for="code">Code à 6 chiffres</label>
        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required>
        <button>Me connecter</button>
    </form>
    <p><a href="{{ route('login') }}">Demander un nouveau code</a></p>
</section>
@endsection
