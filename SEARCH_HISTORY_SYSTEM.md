# Système d'historique de recherche et alertes - Autodz

## Configuration actuelle

### Historique de recherche
- **Nombre de recherches enregistrées :** ILLIMITÉ (toutes les recherches sont sauvegardées)
- **Nombre affiché dans l'interface :** 10 dernières recherches (modifiable dans SearchHistoryController ligne 18)
- **Enregistrement automatique :** OUI, à chaque recherche effectuée avec au moins un filtre

### Paramètres enregistrés
- `marque` - Marque du véhicule
- `modele` - Modèle du véhicule
- `price_max` - Prix maximum
- `annee_min` - Année minimum
- `annee_max` - Année maximum
- `km_min` - Kilométrage minimum
- `km_max` - Kilométrage maximum
- `carburant` - Type de carburant
- `wilaya` - Localisation (ville/wilaya)
- `vehicle_type` - Type de véhicule (voiture/utilitaire/moto)

### Système d'alertes
**Statut :** ✅ CRÉÉ ET FONCTIONNEL

**Fonctionnement :**
1. L'utilisateur effectue une recherche → enregistrée dans `search_histories`
2. L'utilisateur clique sur "Créer une alerte" depuis l'historique
3. L'alerte est créée avec les MÊMES paramètres de recherche
4. Stockée dans la table `search_alerts` avec `is_active = true`

**Tables de base de données :**
- `search_histories` - Historique de toutes les recherches
- `search_alerts` - Alertes actives des utilisateurs

**Prochaines étapes suggérées :**
- [ ] Créer un job/commande pour envoyer des notifications email quand une nouvelle annonce correspond à une alerte
- [ ] Ajouter une notification dans l'interface web quand une alerte matche

## Déploiement Laravel Cloud

**IMPORTANT :** Les migrations doivent être exécutées manuellement sur Laravel Cloud.

### Commandes à exécuter sur le serveur :
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

### En cas d'erreur 500 sur l'historique :
1. Vérifier que les migrations ont bien été exécutées
2. Vérifier les logs : `storage/logs/laravel.log`
3. Re-exécuter les migrations si nécessaire

## Modification du nombre de recherches affichées

Pour changer le nombre de recherches affichées (actuellement 10) :

**Fichier :** `app/Http/Controllers/SearchHistoryController.php`

```php
public function index()
{
    $searches = SearchHistory::where('user_id', auth()->id())
        ->latest()
        ->take(10)  // ← Changer ce nombre
        ->get();
```

## Pages et routes

- `/historique-recherche` - Page d'historique de recherche
- `POST /alertes/creer` - Créer une alerte depuis une recherche
- `DELETE /alertes/{id}` - Supprimer une alerte
