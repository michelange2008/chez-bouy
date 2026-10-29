<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
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

    public function test_une_langue_non_supportee_est_refusee(): void
    {
        $this->withSession(['locale' => 'it'])
            ->post('/langue', ['locale' => 'de'])
            ->assertStatus(422)
            ->assertSessionHas('locale', 'it');
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
            $message = new Message(new \Symfony\Component\Mime\Email());
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
