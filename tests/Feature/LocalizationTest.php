<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'app.locale' => 'fr',
            'app.fallback_locale' => 'fr',
            'cache.default' => 'array',
            'session.driver' => 'array',
        ]);
    }

    public function test_la_langue_par_defaut_est_le_francais(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('lang="fr"', false)
            ->assertSee('Prenez le temps.');
    }

    public function test_le_choix_de_langue_persiste_dans_la_session(): void
    {
        foreach (['en' => 'Take your time.', 'it' => 'Prendetevi il vostro tempo.', 'fr' => 'Prenez le temps.'] as $locale => $text) {
            $this->post('/langue', ['locale' => $locale])
                ->assertRedirect(route('home'))
                ->assertSessionHas('locale', $locale);

            $this->get('/')->assertOk()
                ->assertSee('lang="'.$locale.'"', false)
                ->assertSee($text);
        }
    }

    public function test_le_changement_de_langue_revient_sur_la_page_espace(): void
    {
        $user = User::factory()->create(['role' => 'locataire']);

        $this->actingAs($user)->from('/espace')
            ->post('/langue', ['locale' => 'en'])
            ->assertRedirect('/espace')
            ->assertSessionHas('locale', 'en');

        $this->get('/espace')->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('Your guest area is ready for the welcome guide and practical information for your stay.');
    }

    public function test_une_langue_non_supportee_est_refusee(): void
    {
        $this->withSession(['locale' => 'it'])
            ->post('/langue', ['locale' => 'de'])
            ->assertStatus(422)
            ->assertSessionHas('locale', 'it');
    }

    public function test_une_langue_ajoutee_a_la_configuration_est_disponible(): void
    {
        config(['languages.supported.de' => 'Deutsch']);

        $this->post('/langue', ['locale' => 'de'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'de');

        $this->get('/')->assertOk()
            ->assertSee('lang="de"', false)
            ->assertSee('aria-label="Deutsch"', false)
            ->assertSee('Prenez le temps.');
    }

    public function test_une_langue_retiree_de_la_configuration_est_refusee(): void
    {
        config(['languages.supported' => ['fr' => 'Français', 'en' => 'English']]);

        $this->withSession(['locale' => 'it'])->get('/')->assertOk()
            ->assertSee('lang="fr"', false)
            ->assertDontSee('value="it"', false);

        $this->post('/langue', ['locale' => 'it'])->assertUnprocessable();
    }

    public function test_la_validation_utilise_la_langue_selectionnee(): void
    {
        $this->withSession(['locale' => 'it'])
            ->post('/connexion', ['email' => 'invalide'])
            ->assertSessionHasErrors([
                'email' => 'Il campo indirizzo e-mail deve essere un indirizzo e-mail valido.',
            ]);
    }

    public function test_le_mail_de_connexion_est_traduit(): void
    {
        $user = User::factory()->create(['role' => 'locataire']);
        Mail::shouldReceive('raw')->once()->withArgs(function ($body, $callback) use ($user) {
            $this->assertStringContainsString('Your Chez Bouy code:', $body);
            $message = new Message(new Email);
            $callback($message);
            $this->assertSame('Your login code — Chez Bouy', $message->getSymfonyMessage()->getSubject());
            $this->assertSame($user->email, $message->getSymfonyMessage()->getTo()[0]->getAddress());

            return true;
        });

        $this->withSession(['locale' => 'en'])
            ->post('/connexion', ['email' => $user->email])
            ->assertRedirect(route('code.form'));
    }

    public function test_la_deconnexion_conserve_la_langue(): void
    {
        $user = User::factory()->create(['role' => 'locataire']);
        $this->actingAs($user)->withSession(['locale' => 'it'])
            ->post('/deconnexion')
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'it');
        $this->assertGuest();
    }
}
