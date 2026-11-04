<?php

namespace Tests\Feature;

use App\Models\StaySection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StayMarkdownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_l_apercu_et_la_page_utilisent_le_meme_markdown_securise(): void
    {
        $body = "## Accès\n\n**Important** et *utile*\nseconde ligne\n\n- Porte\n- Clé\n\n[Plan](https://example.com)\n\n<script>alert(1)</script>\n\n<img src=x onerror=alert(1)>\n\n![externe](https://example.com/photo.jpg)\n\n[attaque](javascript:alert%281%29)\n\n[encodée](jav&#x61;script:alert%281%29)\n\n[données](data:text/html,test)";
        $section = StaySection::factory()->create(['published' => true, 'translations' => ['fr' => ['title' => 'Titre', 'body' => $body]]]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->postJson(route('admin.stay.preview'), ['body' => $body])->assertOk();
        $html = $response->json('html');
        foreach (['<h2>Accès</h2>', '<strong>Important</strong>', '<em>utile</em>', '<li>Porte</li>', '<a href="https://example.com">Plan</a>', "<br>\nseconde ligne"] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        foreach (['<script', '<img', 'onerror', 'javascript:', 'data:text/html', 'photo.jpg'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $html);
        }
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        $this->get('/espace/arrivee')->assertOk()->assertSee($html, false);
        $this->assertSame($body, $section->fresh()->translations['fr']['body']);
    }

    public function test_l_apercu_est_reserve_aux_administrateurs_et_valide_le_texte(): void
    {
        $this->post(route('admin.stay.preview'), ['body' => 'texte'])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        $this->postJson(route('admin.stay.preview'), ['body' => 'texte'])->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->postJson(route('admin.stay.preview'), [])->assertJsonValidationErrors('body');
        $this->postJson(route('admin.stay.preview'), ['body' => ['texte']])->assertJsonValidationErrors('body');
        $this->postJson(route('admin.stay.preview'), ['body' => str_repeat('a', 50001)])->assertJsonValidationErrors('body');
        $this->postJson(route('admin.stay.preview'), ['body' => ''])->assertOk()->assertJsonPath('html', '');
        $this->assertDatabaseCount('stay_sections', 0);
    }

    public function test_les_trois_traductions_sont_mises_en_forme(): void
    {
        StaySection::factory()->create(['published' => true, 'translations' => [
            'fr' => ['body' => '**Bonjour**'], 'en' => ['body' => '**Hello**'], 'it' => ['body' => '**Buongiorno**'],
        ]]);
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        foreach (['fr' => 'Bonjour', 'en' => 'Hello', 'it' => 'Buongiorno'] as $locale => $word) {
            $this->withSession(['locale' => $locale])->get('/espace/arrivee')->assertOk()->assertSee('<strong>'.$word.'</strong>', false);
        }
    }
}
