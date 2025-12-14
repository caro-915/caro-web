<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Démarrer (ou réutiliser) une conversation depuis une annonce
    public function start(Request $request, Annonce $annonce)
    {
        $user   = Auth::user();
        $seller = $annonce->user;

        // Empêcher d'écrire à soi-même
        if (!$seller || $seller->id === $user->id) {
            return redirect()
                ->back()
                ->with('error', 'Vous ne pouvez pas vous envoyer de message.');
        }

        // Récupérer ou créer la conversation
        $conversation = Conversation::firstOrCreate(
            [
                'annonce_id' => $annonce->id,
                'buyer_id'   => $user->id,
                'seller_id'  => $seller->id,
            ],
            [
                'last_message_at' => now(),
            ]
        );

        // Si un message initial est envoyé (optionnel)
        if ($request->filled('body')) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $user->id,
                'body'            => $request->input('body'),
            ]);

            $conversation->update(['last_message_at' => now()]);
        }

        return redirect()->route('messages.show', $conversation);
    }

    // Liste des conversations de l'utilisateur
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::with(['annonce', 'buyer', 'seller'])
            ->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            })
            ->orderByDesc('last_message_at')
            ->get();

        return view('messages.index', compact('conversations'));
    }

    // Afficher une conversation
    public function show(Conversation $conversation)
    {
        $userId = Auth::id();

        // Vérification stricte : seul buyer ou seller autorisé
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            abort(403, 'Cette conversation ne vous appartient pas.');
        }

        $conversation->load(['annonce', 'buyer', 'seller', 'messages.sender']);

        // Marquer les messages de l'autre comme lus
        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $userId)
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        return view('messages.show', compact('conversation', 'messages'));
    }

    // Envoyer un message dans une conversation
    public function store(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
                  // Vérification stricte : seul buyer ou seller autorisé
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            abort(403, 'Cette conversation ne vous appartient pas.');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $userId,
            'body'            => $data['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return redirect()->route('messages.show', $conversation);
    }

    public function fetchNew(Request $request, Conversation $conversation)
{
    $user = $request->user();

    // Sécurité : l'utilisateur doit être buyer ou seller de la conversation
    if (! in_array($user->id, [$conversation->buyer_id, $conversation->seller_id])) {
        abort(403);
    }

    $lastId = $request->query('last_id');

    $messages = $conversation->messages()
        ->with('sender')
        ->when($lastId, function ($q) use ($lastId) {
            $q->where('id', '>', $lastId);
        })
        ->orderBy('id')
        ->get();

    // On marque comme lus les nouveaux messages de l'autre utilisateur
    if ($messages->isNotEmpty()) {
        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update(['read_at' => now()]);
    }

    return response()->json([
        'messages' => $messages->map(function ($m) use ($user) {
            return [
                'id'               => $m->id,
                'body'             => $m->body,
                'sender_id'        => $m->sender_id,
                'sender_name'      => $m->sender->name,
                'created_at_human' => $m->created_at->format('d/m/Y H:i'),
            ];
        }),
    ]);
}
}
