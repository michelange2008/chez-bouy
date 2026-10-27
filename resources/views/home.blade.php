@extends('layout')
@section('content')
<section class="intro">
    <p>BIENVENUE CHEZ BOUY</p>
    <h1>Prenez le temps.<br>Vous êtes chez vous.</h1>
    <p>Découvrez les lieux en images et préparez votre prochain séjour.</p>
    <a class="button" href="#photos">Découvrir les photos</a>
</section>
<section id="photos" aria-labelledby="photos-title">
    <h2 id="photos-title">Un aperçu des lieux</h2>
    <div class="gallery">
        @foreach([
    'montagne.jpg' => 'La vue sur les montagnes',
    'residence.png' => 'La résidence — Chez Bouy',
] as $file => $label)
    <figure>
        @if(\Illuminate\Support\Facades\Storage::disk('public')->exists("photos/{$file}"))
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url("photos/{$file}") }}"
                alt="{{ $label }}"
                loading="lazy"
                width="800"
                height="600"
            >
        @else
            <div class="placeholder">Photo à ajouter</div>
        @endif
        <figcaption>{{ $label }}</figcaption>
    </figure>
@endforeach
    </div>
</section>
@endsection
