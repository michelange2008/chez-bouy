<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    public function test_les_notes_sont_reservees_aux_administrateurs(): void
    {
        $note = Note::factory()->create();
        $this->get('/admin/notes')->assertRedirect('/connexion');
        $this->post('/admin/notes', [])->assertRedirect('/connexion');
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        foreach (['/admin/notes', '/admin/notes/ajouter', "/admin/notes/$note->id/modifier"] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/notes', [])->assertForbidden();
        $this->patch("/admin/notes/$note->id", [])->assertForbidden();
        $this->delete("/admin/notes/$note->id")->assertForbidden();
        $this->assertModelExists($note);
    }

    public function test_un_admin_peut_ajouter_modifier_et_supprimer_une_note(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin')->assertOk()->assertSee(route('admin.notes'));
        $this->get('/admin/notes')->assertOk()->assertSee('Aucune note.');
        $this->get('/admin/notes/ajouter')->assertOk()->assertSee('Ajouter une note');
        $this->post('/admin/notes', ['title' => 'Préparer le chalet'])->assertSessionHasNoErrors()->assertRedirect('/admin/notes');
        $note = Note::query()->sole();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => 'Préparer le chalet', 'description' => null]);
        $this->get('/admin/notes')->assertOk()->assertSee('Préparer le chalet');
        $this->get("/admin/notes/$note->id/modifier")->assertOk()->assertSee('Préparer le chalet');
        $this->patch("/admin/notes/$note->id", ['title' => 'Avant arrivée', 'description' => 'Vérifier les clés.'])->assertRedirect('/admin/notes');
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => 'Avant arrivée', 'description' => 'Vérifier les clés.']);
        $this->patch("/admin/notes/$note->id", ['title' => 'Avant arrivée', 'description' => ''])->assertRedirect('/admin/notes');
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'description' => null]);
        $this->delete("/admin/notes/$note->id")->assertRedirect('/admin/notes');
        $this->assertModelMissing($note);
    }

    public function test_les_champs_invalides_ne_sont_pas_enregistres(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/notes', ['title' => '   '])->assertSessionHasErrors('title');
        $this->post('/admin/notes', ['title' => str_repeat('a', 256), 'description' => str_repeat('a', 50001)])->assertSessionHasErrors(['title', 'description']);
        $this->post('/admin/notes', ['title' => ['invalid'], 'description' => ['invalid']])->assertSessionHasErrors(['title', 'description']);
        $this->assertDatabaseCount('notes', 0);
        $note = Note::factory()->create(['title' => 'Titre initial']);
        $this->patch("/admin/notes/$note->id", ['title' => ''])->assertSessionHasErrors('title');
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => 'Titre initial']);
    }

    public function test_les_notes_sont_paginees_et_les_textes_echappes(): void
    {
        Note::factory()->create(['title' => 'Ancienne note']);
        Note::factory()->count(11)->create();
        $note = Note::factory()->create(['title' => '<script>titre</script>', 'description' => '<script>description</script>']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/notes')
            ->assertOk()->assertSee($note->title)->assertSee($note->description)
            ->assertDontSee($note->title, false)->assertDontSee($note->description, false)->assertDontSee('Ancienne note');
        $this->get('/admin/notes?page=2')->assertOk()->assertSee('Ancienne note');
        $this->get("/admin/notes/$note->id/modifier")->assertOk()->assertSee($note->title)->assertDontSee($note->title, false);
    }

    public function test_l_auteur_est_le_createur_connecte_et_reste_inchange_apres_modification(): void
    {
        $author = User::factory()->create(['role' => 'admin', 'first_name' => 'Marie', 'name' => 'Dupont']);
        $editor = User::factory()->create(['role' => 'admin']);
        $this->actingAs($author)->post('/admin/notes', ['title' => 'Accueil', 'author_id' => $editor->id])->assertRedirect('/admin/notes');
        $note = Note::query()->sole();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'author_id' => $author->id]);
        $this->get('/admin/notes')->assertOk()->assertSee('(Marie Dupont)');

        $this->actingAs($editor)->patch("/admin/notes/$note->id", ['title' => 'Accueil modifié', 'author_id' => $editor->id])->assertRedirect('/admin/notes');
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => 'Accueil modifié', 'author_id' => $author->id]);
        $this->get('/admin/notes')->assertOk()->assertSee('(Marie Dupont)');
    }

    public function test_les_notes_sans_auteur_et_celles_d_un_utilisateur_supprime_restent_accessibles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $oldNote = Note::factory()->create(['author_id' => null]);
        $author = User::factory()->create();
        $note = Note::factory()->for($author, 'author')->create();
        $author->delete();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'author_id' => null]);
        $this->actingAs($admin)->get('/admin/notes')->assertOk()->assertSee('(Auteur non renseigné)');
        $this->patch("/admin/notes/$oldNote->id", ['title' => 'Ancienne note modifiée'])->assertRedirect('/admin/notes');
        $this->assertDatabaseHas('notes', ['id' => $oldNote->id, 'author_id' => null]);
    }

    public function test_une_note_absente_retourne_404(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/notes/999/modifier')->assertNotFound();
        $this->patch('/admin/notes/999', ['title' => 'Note'])->assertNotFound();
        $this->delete('/admin/notes/999')->assertNotFound();
    }
}
