<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Stopwords to remove when extracting search terms.
     */
    private array $stopwords = [
        'une', 'un', 'des', 'de', 'la', 'le', 'les', 'du', 'au', 'aux',
        'voiture', 'voitures', 'auto', 'autos', 'vehicule', 'vehicules',
        'moto', 'motos', 'utilitaire', 'utilitaires', 'occasion',
        'je', 'tu', 'il', 'nous', 'vous', 'ils', 'me', 'te', 'se',
        'pour', 'avec', 'dans', 'sur', 'par', 'en', 'a', 'et', 'ou',
        'qui', 'que', 'quoi', 'dont', 'ce', 'cette', 'ces',
        'mon', 'ma', 'mes', 'ton', 'ta', 'tes', 'son', 'sa', 'ses',
        'cher', 'chere', 'pas', 'trop', 'bien', 'bon', 'bonne',
    ];

    /**
     * Intent patterns with synonyms.
     */
    private array $intents = [
        'deposer' => [
            'keywords' => [
                'deposer', 'depose', 'poster', 'publier', 'publie',
                'mettre annonce', 'ajouter annonce', 'creer annonce', 'nouvelle annonce',
                'vendre ma', 'vendre mon', 'mettre en vente', 'je vends', 'a vendre',
            ],
            'reply' => 'Pour déposer une annonce, cliquez sur le bouton ci-dessous. Vous pourrez ajouter les détails de votre véhicule et jusqu\'à 5 photos. C\'est gratuit ! 🚗',
            'action' => 'annonces.create',
            'action_label' => '📝 Déposer une annonce',
        ],
        'contact' => [
            'keywords' => [
                'contact', 'contacter', 'email', 'mail', 'support', 'aide',
                'probleme', 'souci', 'bug', 'signaler', 'reclamation',
                'question', 'demande', 'besoin aide', 'assistance',
            ],
            'reply' => 'Vous pouvez nous contacter via notre formulaire. Nous répondons généralement sous 24-48h. 📧',
            'action' => 'contact.show',
            'action_label' => '📧 Nous contacter',
        ],
        'favoris' => [
            'keywords' => [
                'favoris', 'favori', 'favorite', 'sauvegarde', 'enregistre',
                'like', 'likes', 'coeur', 'cœur', 'aime', 'mes likes',
                'mes sauvegardes', 'annonces sauvegardees',
            ],
            'reply' => 'Retrouvez toutes vos annonces favorites en un clic. Connectez-vous pour y accéder. ❤️',
            'action' => 'favorites.index',
            'action_label' => '❤️ Mes favoris',
        ],
        'messages' => [
            'keywords' => [
                'message', 'messages', 'messagerie', 'discussion', 'discussions',
                'discuter', 'conversation', 'conversations', 'chat', 'chats',
                'repondre', 'contacter vendeur', 'contacter acheteur',
                'ecrire', 'envoyer message', 'mes messages',
            ],
            'reply' => 'Accédez à votre messagerie pour voir vos conversations avec les vendeurs et acheteurs. 💬',
            'action' => 'messages.index',
            'action_label' => '💬 Mes messages',
        ],
        'compte' => [
            'keywords' => [
                'compte', 'connexion', 'connecter', 'deconnecter', 'inscription',
                'inscrire', 'login', 'register', 'profil', 'mon compte',
                'mot de passe', 'password', 'identifiant', 'se connecter',
            ],
            'reply' => 'Gérez votre compte ElSayara : connexion, inscription ou modification de profil. 👤',
            'action' => 'login',
            'action_label' => '👤 Se connecter',
        ],
        'mes_annonces' => [
            'keywords' => [
                'mes annonces', 'mon annonce', 'mes vehicules', 'mes voitures',
                'gerer annonce', 'modifier annonce', 'supprimer annonce',
                'mes publications', 'annonces publiees',
            ],
            'reply' => 'Consultez et gérez toutes vos annonces publiées. 📋',
            'action' => 'my.annonces',
            'action_label' => '📋 Mes annonces',
        ],
        'prix' => [
            'keywords' => [
                'prix', 'tarif', 'cout', 'gratuit', 'payant', 'combien',
                'abonnement', 'payer', 'argent', 'frais', 'commission',
            ],
            'reply' => 'Publier une annonce sur ElSayara est 100% gratuit ! Des options PRO sont disponibles pour booster la visibilité de vos annonces. 💰',
            'action' => null,
            'action_label' => null,
        ],
        'salutation' => [
            'keywords' => [
                'bonjour', 'salut', 'hello', 'hi', 'coucou', 'bonsoir',
                'hey', 'salam', 'bsr', 'bjr', 'yo', 'wesh',
            ],
            'reply' => 'Bonjour ! 👋 Je suis l\'assistant ElSayara. Comment puis-je vous aider aujourd\'hui ?',
            'action' => null,
            'action_label' => null,
        ],
        'merci' => [
            'keywords' => [
                'merci', 'thanks', 'thank', 'parfait', 'super', 'genial',
                'excellent', 'top', 'nickel', 'cool', 'ok merci', 'daccord',
            ],
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
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:500',
            ]);

            $originalMessage = $validated['message'];
            $normalizedMessage = $this->normalizeText($originalMessage);

            // 1. Check for search intent first (higher priority)
            $searchResult = $this->handleSearchIntent($normalizedMessage);
            if ($searchResult) {
                return response()->json($searchResult);
            }

            // 2. Find matching standard intent
            $matchedIntent = $this->findIntent($normalizedMessage);
            if ($matchedIntent) {
                return response()->json($this->buildResponse(
                    $matchedIntent['reply'],
                    $matchedIntent['action'] ? [[
                        'label' => $matchedIntent['action_label'],
                        'url' => route($matchedIntent['action']),
                    ]] : []
                ));
            }

            // 3. No match - return helpful fallback
            Log::info('Chatbot: No intent matched', [
                'original' => $originalMessage,
                'normalized' => $normalizedMessage,
                'ip' => $request->ip(),
            ]);

            return response()->json($this->buildFallbackResponse());

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            
            return response()->json($this->buildResponse(
                'Désolé, une erreur est survenue. Veuillez réessayer.',
                [],
                ['Rechercher', 'Déposer', 'Contact']
            ));
        }
    }

    /**
     * Normalize text: lowercase, remove accents, punctuation, extra spaces.
     */
    private function normalizeText(string $text): string
    {
        // Lowercase
        $text = mb_strtolower($text, 'UTF-8');
        
        // Remove accents (é→e, à→a, etc.)
        $text = Str::ascii($text);
        
        // Remove punctuation except spaces
        $text = preg_replace('/[^\w\s]/u', ' ', $text);
        
        // Collapse multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }

    /**
     * Handle search intent: "je cherche une fabia", "trouver golf", etc.
     */
    private function handleSearchIntent(string $message): ?array
    {
        // Search trigger patterns
        $searchPatterns = [
            '/\b(cherche|recherche|trouver|trouve|veux|voudrais|besoin|acheter)\b/',
            '/^(je cherche|je recherche|je veux|je voudrais)/',
        ];

        $isSearchIntent = false;
        foreach ($searchPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                $isSearchIntent = true;
                break;
            }
        }

        if (!$isSearchIntent) {
            return null;
        }

        // Extract search term by removing trigger words and stopwords
        $searchTerm = $this->extractSearchTerm($message);

        // If no term extracted, ask for clarification
        if (empty($searchTerm)) {
            return $this->buildResponse(
                'Quel modèle ou marque cherchez-vous ? 🔍',
                [],
                ['Clio', 'Golf', 'Polo', '208', 'Fabia', 'Corolla']
            );
        }

        // Try to match with known brand/model
        $match = $this->matchBrandOrModel($searchTerm);

        if ($match) {
            $searchUrl = $this->buildSearchUrl($match);
            $displayName = $match['brand'] && $match['model'] 
                ? "{$match['brand']} {$match['model']}"
                : ($match['model'] ?? $match['brand'] ?? $searchTerm);

            return $this->buildResponse(
                "Super ! Voici les annonces pour {$displayName} 🚗",
                [['label' => "🔍 Voir les {$displayName}", 'url' => $searchUrl]]
            );
        }

        // No exact match, use generic search with term
        $searchUrl = route('annonces.search', ['q' => $searchTerm]);
        
        return $this->buildResponse(
            "Ok, je recherche \"{$searchTerm}\" pour vous 🔍",
            [['label' => "🔍 Rechercher \"{$searchTerm}\"", 'url' => $searchUrl]]
        );
    }

    /**
     * Extract search term from message, removing trigger words and stopwords.
     */
    private function extractSearchTerm(string $message): string
    {
        // Remove search trigger words
        $triggers = [
            'je cherche', 'je recherche', 'je veux', 'je voudrais', 'je veux acheter',
            'cherche', 'recherche', 'trouver', 'trouve', 'veux', 'voudrais',
            'besoin', 'acheter', 'avoir',
        ];

        $term = $message;
        foreach ($triggers as $trigger) {
            $term = str_replace($trigger, '', $term);
        }

        // Remove stopwords
        $words = explode(' ', trim($term));
        $filteredWords = array_filter($words, function ($word) {
            return !in_array($word, $this->stopwords) && strlen($word) > 1;
        });

        return trim(implode(' ', $filteredWords));
    }

    /**
     * Try to match search term with known brand or model in database.
     */
    private function matchBrandOrModel(string $term): ?array
    {
        $term = trim($term);
        if (empty($term)) {
            return null;
        }

        // Split into words for multi-word searches like "renault clio"
        $words = explode(' ', $term);

        // Try to find model first (more specific)
        $model = CarModel::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($term) . '%'])
            ->with('brand')
            ->first();

        if ($model) {
            return [
                'brand' => $model->brand?->name,
                'model' => $model->name,
            ];
        }

        // Try to find brand
        $brand = CarBrand::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($term) . '%'])
            ->first();

        if ($brand) {
            // Check if there's a second word that could be a model
            if (count($words) > 1) {
                $possibleModelName = end($words);
                $modelInBrand = CarModel::where('car_brand_id', $brand->id)
                    ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($possibleModelName) . '%'])
                    ->first();
                
                if ($modelInBrand) {
                    return [
                        'brand' => $brand->name,
                        'model' => $modelInBrand->name,
                    ];
                }
            }

            return [
                'brand' => $brand->name,
                'model' => null,
            ];
        }

        // Try each word individually
        foreach ($words as $word) {
            if (strlen($word) < 2) continue;

            $model = CarModel::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($word) . '%'])
                ->with('brand')
                ->first();

            if ($model) {
                return [
                    'brand' => $model->brand?->name,
                    'model' => $model->name,
                ];
            }

            $brand = CarBrand::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($word) . '%'])
                ->first();

            if ($brand) {
                return [
                    'brand' => $brand->name,
                    'model' => null,
                ];
            }
        }

        return null;
    }

    /**
     * Build search URL with brand/model parameters.
     */
    private function buildSearchUrl(array $match): string
    {
        $params = [];
        
        if (!empty($match['brand'])) {
            $params['marque'] = $match['brand'];
        }
        if (!empty($match['model'])) {
            $params['modele'] = $match['model'];
        }

        return route('annonces.search', $params);
    }

    /**
     * Find intent matching the user message.
     */
    private function findIntent(string $message): ?array
    {
        foreach ($this->intents as $intent) {
            foreach ($intent['keywords'] as $keyword) {
                // Use word boundary for short keywords to avoid false positives
                if (strlen($keyword) <= 3) {
                    if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/', $message)) {
                        return $intent;
                    }
                } else {
                    if (str_contains($message, $keyword)) {
                        return $intent;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Build a standardized response array.
     */
    private function buildResponse(string $reply, array $actions = [], array $quickReplies = []): array
    {
        return [
            'reply' => $reply,
            'actions' => $actions,
            'quick_replies' => $quickReplies,
        ];
    }

    /**
     * Build fallback response when no intent matches.
     */
    private function buildFallbackResponse(): array
    {
        return $this->buildResponse(
            'Je ne suis pas sûr de comprendre, mais je peux vous aider avec ces actions :',
            [
                ['label' => '🔍 Rechercher un véhicule', 'url' => route('annonces.search')],
                ['label' => '📝 Déposer une annonce', 'url' => route('annonces.create')],
                ['label' => '❤️ Mes favoris', 'url' => route('favorites.index')],
                ['label' => '💬 Mes messages', 'url' => route('messages.index')],
                ['label' => '📧 Nous contacter', 'url' => route('contact.show')],
            ],
            ['Rechercher', 'Déposer', 'Favoris', 'Messages', 'Contact']
        );
    }

    /**
     * Test examples for documentation.
     * Run: php artisan tinker --execute="app(App\Http\Controllers\ChatbotController::class)->testExamples()"
     */
    public function testExamples(): array
    {
        $examples = [
            'bonjour',
            'je cherche une fabia',
            'trouver une golf',
            'deposer annonce',
            'mes favoris',
            'contact support',
            'renault clio occasion',
            'blablabla random text',
            'merci beaucoup',
            'comment vendre ma voiture',
        ];

        $results = [];
        foreach ($examples as $input) {
            $normalized = $this->normalizeText($input);
            
            // Check search first
            $searchResult = $this->handleSearchIntent($normalized);
            if ($searchResult) {
                $results[$input] = [
                    'normalized' => $normalized,
                    'response' => $searchResult,
                ];
                continue;
            }

            // Check standard intents
            $intent = $this->findIntent($normalized);
            if ($intent) {
                $results[$input] = [
                    'normalized' => $normalized,
                    'response' => $this->buildResponse(
                        $intent['reply'],
                        $intent['action'] ? [['label' => $intent['action_label'], 'url' => '...']] : []
                    ),
                ];
                continue;
            }

            // Fallback
            $results[$input] = [
                'normalized' => $normalized,
                'response' => $this->buildFallbackResponse(),
            ];
        }

        return $results;
    }
}
