@extends('layout')
@section('title', 'Mon séjour')
@section('content')
<h1>Bienvenue, {{ auth()->user()->name }}.</h1>
<p>Votre espace locataire est prêt à accueillir le livret d’accueil et les informations pratiques du séjour.</p>
@endsection
