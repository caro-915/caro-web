<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Annonce;
use Illuminate\Http\Request;

class MessageApiController extends Controller
{
    /**
     * Liste des conversations
     * GET /api/conversations
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->with(['annonce', 'buyer', 'seller', 'messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get();

        $data = $conversations->map(function($conversation) use ($userId) {
            $otherUser = $conversation->buyer_id === $userId 
                ? $conversation->seller 
                : $conversation->buyer;

            $lastMessage = $conversation->messages->first();
            
            $unreadCount = Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->count();

            return [
                'id' => $conversation->id,
                'annonce' => [
                    'id' => $conversation->annonce->id,
                    'title' => $conversation->annonce->titre,
                    'image' => $conversation->annonce->image_path 
                        ? url('storage/' . $conversation->annonce->image_path) 
                        : null,
                ],
                'otherUser' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->avatar ? url('storage/' . $otherUser->avatar) : null,
                ],
                'lastMessage' => $lastMessage ? [
                    'content' => $lastMessage->body,
                    'sentAt' => $lastMessage->created_at->toIso8601String(),
                    'isMe' => $lastMessage->sender_id === $userId,
                ] : null,
                'unreadCount' => $unreadCount,
                'lastMessageAt' => $conversation->last_message_at->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Détail d'une conversation avec messages
     * GET /api/conversations/{id}
     */
    public function show(Request $request, $id)
    {
        $conversation = Conversation::with(['annonce', 'buyer', 'seller'])
            ->findOrFail($id);

        // Check if user is part of the conversation
        $userId = $request->user()->id;
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            return response()->json([
                'message' => 'Accès non autorisé',
            ], 403);
        }

        // Mark messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $otherUser = $conversation->buyer_id === $userId 
            ? $conversation->seller 
            : $conversation->buyer;

        return response()->json([
            'id' => $conversation->id,
            'annonce' => [
                'id' => $conversation->annonce->id,
                'title' => $conversation->annonce->titre,
                'image' => $conversation->annonce->image_path 
                    ? url('storage/' . $conversation->annonce->image_path) 
                    : null,
            ],
            'otherUser' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'avatar' => $otherUser->avatar ? url('storage/' . $otherUser->avatar) : null,
            ],
            'messages' => $messages->map(function($message) use ($userId) {
                return [
                    'id' => $message->id,
                    'content' => $message->body,
                    'sentAt' => $message->created_at->toIso8601String(),
                    'isMe' => $message->sender_id === $userId,
                    'isRead' => $message->read_at !== null,
                ];
            }),
        ]);
    }

    /**
     * Envoyer un message
     * POST /api/messages
     */
    public function store(Request $request)
    {
        $request->validate([
            'annonce_id' => 'required|exists:annonces,id',
            'content' => 'required|string|max:2000',
        ]);

        $annonce = Annonce::findOrFail($request->annonce_id);
        $userId = $request->user()->id;
        $sellerId = $annonce->user_id;

        // Trouver ou créer la conversation
        // Déterminer qui est le buyer et qui est le seller
        $isSeller = ($userId === $sellerId);
        
        if ($isSeller) {
            // Le vendeur répond : chercher une conversation existante où il est seller
            $conversation = Conversation::where('annonce_id', $annonce->id)
                ->where('seller_id', $userId)
                ->first();
            
            if (!$conversation) {
                return response()->json([
                    'message' => 'Aucune conversation trouvée. Le vendeur ne peut pas initier une conversation.',
                ], 422);
            }
        } else {
            // L'acheteur envoie : créer ou trouver la conversation
            $conversation = Conversation::firstOrCreate(
                [
                    'annonce_id' => $annonce->id,
                    'buyer_id' => $userId,
                    'seller_id' => $sellerId,
                ],
                [
                    'last_message_at' => now(),
                ]
            );
        }

        // Créer le message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $request->content,
        ]);

        // Mettre à jour le timestamp de la conversation
        $conversation->update([
            'last_message_at' => now(),
        ]);

        return response()->json([
            'message' => 'Message envoyé avec succès',
            'data' => [
                'id' => $message->id,
                'content' => $message->body,
                'sentAt' => $message->created_at->toIso8601String(),
                'isMe' => true,
                'isRead' => false,
            ],
        ], 201);
    }

    /**
     * Marquer les messages d'une conversation comme lus
     * POST /api/conversations/{id}/read
     */
    public function markAsRead(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);

        // Check if user is part of the conversation
        $userId = $request->user()->id;
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            return response()->json([
                'message' => 'Accès non autorisé',
            ], 403);
        }

        // Mark all messages from the other user as read
        $updated = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Messages marqués comme lus',
            'updated' => $updated,
        ]);
    }}