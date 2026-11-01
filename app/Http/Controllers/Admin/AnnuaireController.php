<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveAnnuaireRequest;
use App\Models\Annuaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnuaireController extends Controller
{
    public function index(): View
    {
        return view('admin.annuaire', ['contacts' => Annuaire::query()->orderBy('nom')->orderBy('id')->paginate(12)]);
    }

    public function create(): View
    {
        return view('admin.annuaire.form', ['annuaire' => new Annuaire]);
    }

    public function edit(Annuaire $annuaire): View
    {
        return view('admin.annuaire.form', ['annuaire' => $annuaire]);
    }

    public function store(SaveAnnuaireRequest $request): RedirectResponse
    {
        return $this->save($request, new Annuaire);
    }

    public function update(SaveAnnuaireRequest $request, Annuaire $annuaire): RedirectResponse
    {
        return $this->save($request, $annuaire);
    }

    private function save(SaveAnnuaireRequest $request, Annuaire $annuaire): RedirectResponse
    {
        $data = $request->validated();
        $annuaire->nom = $data['nom'];
        $annuaire->fonction = $data['fonction'] ?? null;
        $annuaire->telephone = $data['telephone'] ?? null;
        $annuaire->mail = $data['mail'] ?? null;
        $annuaire->adresse_postale = $data['adresse_postale'] ?? null;
        $annuaire->observations = $data['observations'] ?? null;
        $annuaire->save();

        return redirect()->route('admin.annuaire')->with('status', __('annuaire.saved'));
    }

    public function destroy(Annuaire $annuaire): RedirectResponse
    {
        $annuaire->delete();

        return redirect()->route('admin.annuaire')->with('status', __('annuaire.deleted'));
    }
}
