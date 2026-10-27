@extends('layout')
@section('title', 'Administration')
@section('content')
<h1>Administration</h1>
<p>Cette page est réservée aux administrateurs.</p>
<p>La gestion des locataires, des photos et des séjours pourra être ajoutée ici.</p>
<a href="{{ route('dashboard') }}">Voir l’espace locataire</a>
@endsection
