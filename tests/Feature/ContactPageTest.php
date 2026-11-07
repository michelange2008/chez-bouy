<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_formulaire_est_accessible_depuis_l_accueil(): void
    {
        $this->withoutVite()->withSession(['locale' => 'fr']);
        $this->get('/')->assertOk()->assertSee('Nous contacter')->assertSee('href="'.route('contact').'"', false);
        $this->get('/contact')->assertOk()->assertSee('Votre message')->assertSee('action="'.route('contact.store').'"', false);
    }

    public function test_le_visiteur_peut_envoyer_un_message_au_proprietaire(): void
    {
        config(['mail.default' => 'array']);
        $data = ['email' => 'visiteur@example.org', 'message' => "Bonjour,\nLe logement est-il disponible ?"];
        $this->withSession(['locale' => 'fr'])->post('/contact', $data)
            ->assertRedirect(route('contact'))->assertSessionHas('status', 'Votre message a bien été envoyé.');

        $mail = Mail::getSymfonyTransport()->messages()->sole()->getOriginalMessage();
        $this->assertSame('chezbouy@michelange.org', $mail->getTo()[0]->getAddress());
        $this->assertSame($data['email'], $mail->getReplyTo()[0]->getAddress());
        $this->assertSame($data['message'], $mail->getTextBody());
    }

    public function test_les_champs_invalides_empechent_l_envoi(): void
    {
        config(['mail.default' => 'array']);
        $this->from('/contact')->post('/contact', ['email' => 'invalide', 'message' => ''])
            ->assertRedirect('/contact')->assertSessionHasErrors(['email', 'message']);
        $this->assertCount(0, Mail::getSymfonyTransport()->messages());
    }

    public function test_le_message_est_conserve_si_le_service_mail_est_indisponible(): void
    {
        Mail::shouldReceive('raw')->once()->andThrow(new TransportException('Service indisponible'));
        $this->post('/contact', ['email' => 'visiteur@example.org', 'message' => 'Bonjour'])
            ->assertRedirect(route('contact'))->assertSessionHasErrors('contact')
            ->assertSessionHasInput('message', 'Bonjour')->assertSessionMissing('status');
    }
}
