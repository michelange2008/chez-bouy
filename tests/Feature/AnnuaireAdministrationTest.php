<?php

namespace Tests\Feature;

use App\Models\Annuaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnuaireAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    public function test_l_annuaire_est_reserve_aux_administrateurs(): void
    {
        $contact = Annuaire::factory()->create();
        $this->get('/admin/annuaire')->assertRedirect('/connexion');
        $this->post('/admin/annuaire', [])->assertRedirect('/connexion');
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        foreach (['/admin/annuaire', '/admin/annuaire/ajouter', "/admin/annuaire/$contact->id/modifier"] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/annuaire', [])->assertForbidden();
        $this->patch("/admin/annuaire/$contact->id", [])->assertForbidden();
        $this->delete("/admin/annuaire/$contact->id")->assertForbidden();
        $this->assertModelExists($contact);
    }

    public function test_un_admin_peut_creer_modifier_et_supprimer_une_fiche(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin')->assertOk()->assertSee(route('admin.annuaire'));
        $this->get('/admin/annuaire')->assertOk()->assertSee('Aucune fiche.');
        $this->get('/admin/annuaire/ajouter')->assertOk()->assertSee('Téléphone');
        $this->post('/admin/annuaire', ['nom' => 'Dupont'])->assertSessionHasNoErrors()->assertRedirect('/admin/annuaire');
        $contact = Annuaire::query()->sole();
        $this->assertDatabaseHas('annuaires', ['id' => $contact->id, 'nom' => 'Dupont', 'fonction' => null, 'telephone' => null, 'mail' => null, 'adresse_postale' => null, 'observations' => null]);
        $data = ['nom' => 'Martin', 'fonction' => 'Plombier', 'telephone' => '+33 (0)6 12 34 56 78', 'mail' => 'martin@example.com', 'adresse_postale' => "12 rue du Chalet\n75000 Paris", 'observations' => 'Disponible le matin'];
        $this->patch("/admin/annuaire/$contact->id", $data)->assertSessionHasNoErrors()->assertRedirect('/admin/annuaire');
        $this->assertDatabaseHas('annuaires', ['id' => $contact->id, ...$data]);
        $this->get('/admin/annuaire')->assertOk()->assertSee('Martin')->assertSee('Plombier')->assertSee('martin@example.com')->assertSee('href="mailto:martin@example.com"', false)->assertSee('Disponible le matin');
        $this->get("/admin/annuaire/$contact->id/modifier")->assertOk()->assertSee('Martin')->assertSee('martin@example.com');
        $this->patch("/admin/annuaire/$contact->id", ['nom' => 'Martin', 'mail' => '', 'telephone' => ''])->assertRedirect('/admin/annuaire');
        $this->assertDatabaseHas('annuaires', ['id' => $contact->id, 'mail' => null, 'telephone' => null, 'fonction' => null, 'adresse_postale' => null, 'observations' => null]);
        $this->delete("/admin/annuaire/$contact->id")->assertRedirect('/admin/annuaire');
        $this->assertModelMissing($contact);
    }

    public function test_la_validation_refuse_les_donnees_invalides(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/annuaire', ['nom' => ' '])->assertSessionHasErrors('nom');
        $this->post('/admin/annuaire', ['nom' => 'Contact', 'mail' => 'invalide'])->assertSessionHasErrors('mail');
        $this->post('/admin/annuaire', [
            'nom' => str_repeat('a', 256), 'fonction' => str_repeat('a', 256), 'telephone' => str_repeat('1', 51),
            'mail' => str_repeat('a', 250).'@example.com', 'adresse_postale' => str_repeat('a', 2001), 'observations' => str_repeat('a', 50001),
        ])->assertSessionHasErrors(['nom', 'fonction', 'telephone', 'mail', 'adresse_postale', 'observations']);
        $this->post('/admin/annuaire', ['nom' => [], 'fonction' => [], 'telephone' => [], 'mail' => [], 'adresse_postale' => [], 'observations' => []])
            ->assertSessionHasErrors(['nom', 'fonction', 'telephone', 'mail', 'adresse_postale', 'observations']);
        $this->assertDatabaseCount('annuaires', 0);
        $contact = Annuaire::factory()->create(['nom' => 'Original']);
        $this->patch("/admin/annuaire/$contact->id", ['nom' => 'Modifié', 'mail' => 'invalide'])->assertSessionHasErrors('mail');
        $this->assertDatabaseHas('annuaires', ['id' => $contact->id, 'nom' => 'Original']);
    }

    public function test_les_fiches_sont_triees_paginees_et_echappees(): void
    {
        Annuaire::factory()->create(['nom' => 'Zoulou']);
        Annuaire::factory()->count(12)->create(['nom' => 'Alpha']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/annuaire')->assertOk()->assertSee('Alpha')->assertDontSee('Zoulou');
        $this->get('/admin/annuaire?page=2')->assertOk()->assertSee('Zoulou');
        $payload = '<script>alert(1)</script>';
        $contact = Annuaire::factory()->create(['nom' => $payload, 'fonction' => $payload, 'telephone' => $payload, 'adresse_postale' => $payload, 'observations' => $payload]);
        $this->get('/admin/annuaire')->assertOk()->assertSee($payload)->assertDontSee($payload, false);
        $this->get("/admin/annuaire/$contact->id/modifier")->assertOk()->assertSee($payload)->assertDontSee($payload, false);
    }

    public function test_les_numeros_francais_sont_espaces_sans_modifier_la_valeur_enregistree(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([
            ['0612345678', '06 12 34 56 78'],
            ['01.23.45.67.89', '01 23 45 67 89'],
            ['+33612345678', '+33 6 12 34 56 78'],
            ['+33 (0)612345678', '+33 (0)6 12 34 56 78'],
            ['0033612345678', '0033 6 12 34 56 78'],
            ['112', '112'],
            ['+39 06 6982', '+39 06 6982'],
        ] as [$stored, $displayed]) {
            $contact = Annuaire::factory()->create(['telephone' => $stored]);
            $this->get('/admin/annuaire')->assertOk()->assertSee($displayed);
            $this->assertDatabaseHas('annuaires', ['id' => $contact->id, 'telephone' => $stored]);
        }
    }

    public function test_une_fiche_absente_retourne_404(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/annuaire/999/modifier')->assertNotFound();
        $this->patch('/admin/annuaire/999', ['nom' => 'Contact'])->assertNotFound();
        $this->delete('/admin/annuaire/999')->assertNotFound();
    }
}
