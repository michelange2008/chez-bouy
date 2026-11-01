@extends('admin.layout')
@section('title', $annuaire->exists ? __('annuaire.edit') : __('annuaire.add'))
@section('admin-content')
<div class="users-toolbar"><h2>{{ $annuaire->exists ? __('annuaire.edit') : __('annuaire.add') }}</h2><a class="media-button" href="{{ route('admin.annuaire') }}">{{ __('annuaire.back') }}</a></div>
<form action="{{ $annuaire->exists ? route('admin.annuaire.update', $annuaire) : route('admin.annuaire.store') }}" method="post" class="media-form">
    @csrf
    @if($annuaire->exists) @method('PATCH') @endif
    <section class="media-panel">
        <label for="annuaire-nom">{{ __('annuaire.nom') }} *</label>
        <input id="annuaire-nom" name="nom" type="text" maxlength="255" value="{{ old('nom', $annuaire->nom) }}" required>
        <label for="annuaire-fonction">{{ __('annuaire.fonction') }}</label>
        <input id="annuaire-fonction" name="fonction" type="text" maxlength="255" value="{{ old('fonction', $annuaire->fonction) }}">
        <label for="annuaire-telephone">{{ __('annuaire.telephone') }}</label>
        <input id="annuaire-telephone" name="telephone" type="tel" maxlength="50" value="{{ old('telephone', $annuaire->telephone) }}">
        <label for="annuaire-mail">{{ __('annuaire.mail') }}</label>
        <input id="annuaire-mail" name="mail" type="email" maxlength="255" value="{{ old('mail', $annuaire->mail) }}">
        <label for="annuaire-adresse_postale">{{ __('annuaire.adresse_postale') }}</label>
        <textarea id="annuaire-adresse_postale" name="adresse_postale" rows="4" maxlength="2000">{{ old('adresse_postale', $annuaire->adresse_postale) }}</textarea>
        <label for="annuaire-observations">{{ __('annuaire.observations') }}</label>
        <textarea id="annuaire-observations" name="observations" rows="4" maxlength="50000">{{ old('observations', $annuaire->observations) }}</textarea>
    </section>
    <div class="users-form-actions"><a class="media-button" href="{{ route('admin.annuaire') }}">{{ __('annuaire.cancel') }}</a><button class="media-button">{{ __('annuaire.save') }}</button></div>
</form>
@endsection
