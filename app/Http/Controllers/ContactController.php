<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Affiche le formulaire de contact.
     */
    public function show()
    {
        return view('contact');
    }

    /**
     * Traite et envoie le message de contact.
     */
    public function send(Request $request)
    {
        // Honeypot anti-spam: si le champ "website" est rempli, c'est un bot
        if ($request->filled('website')) {
            // Simule un succès pour ne pas alerter le bot
            return back()->with('success', 'Votre message a été envoyé avec succès !');
        }

        // Validation des données
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:3000',
        ], [
            'name.required'    => 'Veuillez entrer votre nom.',
            'name.max'         => 'Le nom ne peut pas dépasser 100 caractères.',
            'email.required'   => 'Veuillez entrer votre adresse email.',
            'email.email'      => 'Veuillez entrer une adresse email valide.',
            'subject.required' => 'Veuillez entrer un sujet.',
            'subject.max'      => 'Le sujet ne peut pas dépasser 150 caractères.',
            'message.required' => 'Veuillez entrer votre message.',
            'message.max'      => 'Le message ne peut pas dépasser 3000 caractères.',
        ]);

        // Préparer les données pour l'email
        $data = [
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'subject'    => $validated['subject'],
            'body'       => $validated['message'],
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'sent_at'    => now()->format('d/m/Y à H:i'),
        ];

        try {
            Mail::to(config('autodz.contact_email', 'contact@elsayara.com'))
                ->send(new ContactMessageMail($data));

            return back()->with('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
        } catch (\Exception $e) {
            Log::error('Erreur envoi email contact: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer plus tard.');
        }
    }
}
