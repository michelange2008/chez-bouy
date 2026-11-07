<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            Mail::raw($data['message'], function (Message $mail) use ($data): void {
                $mail->to('chezbouy@michelange.org')
                    ->replyTo($data['email'])
                    ->subject('Chez Bouy — Formulaire de contact');
            });
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return to_route('contact')->withInput()->withErrors(['contact' => __('site.contact_failed')]);
        }

        return to_route('contact.confirmation');
    }
}
