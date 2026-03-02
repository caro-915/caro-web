<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Intent patterns and their responses/actions.
     */
    private array $intents = [
        'deposer' => [
            'keywords' => ['deposer', 'déposer', 'annonce', 'publier', 'vendre', 'poster', 'créer annonce', 'nouvelle annonce'],
            'reply' => 'Pour déposer une annonce, cliquez sur le bouton ci-dessous. Vous pourrez y ajouter les détails de votre véhicule et jusqu\'à 5 photos.',
            'action' => 'annonces.create',
            'action_label' => 'Déposer une annonce',
        ],
        'recherche' => [
            'keywords' => ['recherche', 'rechercher', 'chercher', 'filtre', 'trouver', 'acheter', 'voiture', 'véhicule', 'moto'],
            'reply' => 'Utilisez notre moteur de recherche pour trouver le véhicule idéal. Vous pouvez filtrer par marque, prix, kilométrage et plus.',
            'action' => 'recherche',
            'action_label' => 'Rechercher un véhicule',
        ],
        'contact' => [
            'keywords' => ['contact', 'contacter', 'email', 'support', 'aide', 'problème', 'signaler', 'réclamation'],
            'reply' => 'Vous pouvez nous contacter via notre formulaire. Nous répondons généralement sous 24-48h.',
            'action' => 'contact.show',
            'action_label' => 'Nous contacter',
        ],
        'favoris' => [
            'keywords' => ['favoris', 'favori', 'sauvegardé', 'enregistré', 'like', 'coeur'],
            'reply' => 'Retrouvez toutes vos annonces favorites en un clic. Connectez-vous pour y accéder.',
            'action' => 'favorites.index',
            'action_label' => 'Mes favoris',
        ],
        'messages' => [
            'keywords' => ['message', 'messages', 'messagerie', 'discuter', 'conversation', 'répondre', 'vendeur', 'acheteur'],
            'reply' => 'Accédez à votre messagerie pour voir vos conversations avec les vendeurs et acheteurs.',
            'action' => 'messages.index',
            'action_label' => 'Mes messages',
        ],
        'compte' => [
            'keywords' => ['compte', 'connexion', 'connecter', 'inscription', 'inscrire', 'login', 'register', 'profil', 'mon compte'],
            'reply' => 'Gérez votre compte ElSayara : connexion, inscription ou modification de profil.',
            'action' => 'login',
            'action_label' => 'Se connecter',
        ],
        'mes_annonces' => [
            'keywords' => ['mes annonces', 'mon annonce', 'mes véhicules', 'mes voitures', 'gérer annonce'],
            'reply' => 'Consultez et gérez toutes vos annonces publiées.',
            'action' => 'my.annonces',
            'action_label' => 'Mes annonces',
        ],
        'prix' => [
            'keywords' => ['prix', 'tarif', 'coût', 'gratuit', 'payant', 'combien', 'abonnement'],
            'reply' => 'Publier une annonce sur ElSayara est gratuit ! Des options PRO sont disponibles pour booster vos annonces.',
            'action' => null,
            'action_label' => null,
        ],
        'salutation' => [
            'keywords' => ['bonjour', 'salut', 'hello', 'hi', 'coucou', 'bonsoir', 'hey'],
            'reply' => 'Bonjour ! 👋 Je suis l\'assistant ElSayara. Comment puis-je vous aider aujourd\'hui ?',
            'action' => null,
            'action_label' => null,
        ],
        'merci' => [
            'keywords' => ['merci', 'thanks', 'parfait', 'super', 'génial', 'excellent'],
            'reply' => 'Avec plaisir ! N\'hésitez pas si vous avez d\'autres questions. 😊',
            'action' => null,
            'action_label' => null,
        ],
    ];

    /**
     * Handle chatbot question.
     */
    public function ask(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = strtolower(trim($validated['message']));
        
        // Find matching intent
        $matchedIntent = $this->findIntent($message);

        if ($matchedIntent) {
            $response = [
                'reply' => $matchedIntent['reply'],
                'actions' => [],
            ];

            if ($matchedIntent['action']) {
                $response['actions'][] = [
                    'label' => $matchedIntent['action_label'],
                    'url' => route($matchedIntent['action']),
                ];
            }

            return response()->json($response);
        }

        // No match found - log and return default menu
        Log::info('Chatbot: Question inconnue', [
            'message' => $validated['message'],
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'reply' => 'Je ne suis pas sûr de comprendre votre demande. Voici ce que je peux faire pour vous :',
            'actions' => [
                ['label' => '🚗 Déposer une annonce', 'url' => route('annonces.create')],
                ['label' => '🔍 Rechercher un véhicule', 'url' => route('recherche')],
                ['label' => '❤️ Mes favoris', 'url' => route('favorites.index')],
                ['label' => '💬 Mes messages', 'url' => route('messages.index')],
                ['label' => '📧 Nous contacter', 'url' => route('contact.show')],
            ],
        ]);
    }

    /**
     * Find intent matching the user message.
     */
    private function findIntent(string $message): ?array
    {
        foreach ($this->intents as $intent) {
            foreach ($intent['keywords'] as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $intent;
                }
            }
        }

        return null;
    }
}
