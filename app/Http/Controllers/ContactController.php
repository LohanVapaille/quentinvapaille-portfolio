<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $done = redirect(route('about') . '#contact')
            ->with('success', 'Merci ! Ton message est bien parti, je te réponds très vite.');

        // Honeypot : un robot a rempli le champ caché
        if ($request->filled('website')) {
            return $done;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        ContactMessage::create($data + ['ip' => $request->ip()]);

        try {
            $to = SiteSetting::current()->contact_email;

            if ($to) {
                Mail::raw(
                    "Nom : {$data['name']}\nEmail : {$data['email']}\n\n{$data['message']}",
                    function ($mail) use ($to, $data) {
                        $mail->to($to)
                            ->replyTo($data['email'], $data['name'])
                            ->subject('Nouveau message depuis le portfolio');
                    }
                );
            }
        } catch (Throwable $e) {
            report($e); // le message reste visible dans l'administration
        }

        return $done;
    }
}